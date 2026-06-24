<?php

namespace App\Services\PdfParser;

use Smalot\PdfParser\Parser;

class HapvidaEmpresarialPdfParser
{
    private string $page3    = '';
    private string $page4    = '';
    private string $page5    = '';
    private string $pageBenef = '';
    private string $format   = 'A'; // 'A' = DD/MM/YYYY + R$   'B' = DD MONTHNAME YYYY, sem R$

    private const MONTHS = [
        'JANEIRO'=>'01','FEVEREIRO'=>'02','MARCO'=>'03','MARÇO'=>'03',
        'ABRIL'=>'04','MAIO'=>'05','JUNHO'=>'06','JULHO'=>'07',
        'AGOSTO'=>'08','SETEMBRO'=>'09','OUTUBRO'=>'10',
        'NOVEMBRO'=>'11','DEZEMBRO'=>'12',
    ];

    private const UF_MAP = [
        'ACRE'=>'AC','ALAGOAS'=>'AL','AMAPA'=>'AP','AMAZONAS'=>'AM',
        'BAHIA'=>'BA','CEARA'=>'CE','DISTRITOFEDERAL'=>'DF','ESPIRITOSANTO'=>'ES',
        'GOIAS'=>'GO','MARANHAO'=>'MA','MATOGROSSO'=>'MT','MATOGROSSODOSUL'=>'MS',
        'MINASGERAIS'=>'MG','PARA'=>'PA','PARAIBA'=>'PB','PARANA'=>'PR',
        'PERNAMBUCO'=>'PE','PIAUI'=>'PI','RIODEJANEIRO'=>'RJ',
        'RIOGRANDEDONORTE'=>'RN','RIOGRANDEDOSUL'=>'RS','RONDONIA'=>'RO',
        'RORAIMA'=>'RR','SANTACATARINA'=>'SC','SAOPAULO'=>'SP',
        'SERGIPE'=>'SE','TOCANTINS'=>'TO',
    ];

    public function parse(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);
        $pages  = $pdf->getPages();
        $total  = count($pages);

        $this->page3    = $this->normalizeText($total > 2 ? $pages[2]->getText() : '');
        $this->page4    = $this->normalizeText($total > 3 ? $pages[3]->getText() : '');
        $this->page5    = $this->normalizeText($total > 4 ? $pages[4]->getText() : '');
        $this->pageBenef = $this->normalizeText($this->findBenefPage($pages, $total));

        $this->format = $this->detectFormat();

        return [
            'proposta_nr'         => $this->extractPropostaNr(),
            'cnpj'                => $this->extractCnpj(),
            'razao_social'        => $this->extractRazaoSocial(),
            'cidade'              => $this->extractCidade(),
            'uf'                  => $this->extractUf(),
            'cep'                 => $this->extractCep(),
            'celular'             => $this->extractCelular(),
            'email'               => $this->extractEmail(),
            'responsavel'         => $this->extractResponsavel(),
            'data_vigencia'       => $this->extractDataVigencia(),
            'vencimento_dia'      => $this->extractVencimentoDia(),
            'data_boleto'         => $this->buildDataBoleto(),
            'plano_nome_comercial'=> $this->extractNomeComercialSaude(),
            'codigo_saude'        => $this->extractCodigoComercialSaude(),
            'codigo_ans_saude'    => $this->extractCodigoAnsSaude(),
            'codigo_odonto'       => $this->extractCodigoComercialOdonto(),
            'codigo_ans_odonto'   => $this->extractCodigoAnsOdonto(),
            'vidas'               => $this->extractVidas(),
            'area_atuacao'        => $this->extractAreaAtuacao(),
            'tabela_cidade'       => $this->extractPrimeiraCidade(),
            'codigo_vendedor'     => $this->extractCodigoVendedor(),
            'nome_vendedor'       => $this->extractNomeVendedor(),
            'codigo_corretora'    => $this->extractCodigoCorretora(),
            'valor_plano_saude'   => $this->extractValorSaude(),
            'valor_plano_odonto'  => $this->extractValorOdonto(),
            'taxa_adesao'         => $this->extractTaxaAdesao(),
            'valor_total'         => $this->extractValorTotal(),
            'beneficiarios'       => $this->extractBeneficiarios(),
        ];
    }

    // ─── Format detection ─────────────────────────────────────

    private function detectFormat(): string
    {
        // Format A has R$ values and DD/MM/YYYY date in page 3
        // Format B has text-month date (DD MONTHNAME YYYY) and no R$ in page 3
        if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $this->page3)) {
            return 'A';
        }
        return 'B';
    }

    // ─── Page 3 extractions ────────────────────────────────────

    private function extractCnpj(): string
    {
        if (preg_match('/(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2})/', $this->page3, $m)) {
            return $m[1];
        }
        return '';
    }

    private function extractRazaoSocial(): string
    {
        // Address keywords including AL (Alameda abbreviated)
        $keywords = 'RUA\b|AVENIDA\b|SEGUNDA\b|TERCEIRA\b|TRAVESSA\b|ALAMEDA\b|PRAÇA\b|'
                  . 'RODOVIA\b|ESTRADA\b|AV\.\b|R\.\b|\bAL\b';
        if (preg_match(
            '/\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}\s+(.+?)\s+(?:' . $keywords . ')/u',
            $this->page3, $m
        )) {
            return trim($m[1]);
        }
        return '';
    }

    private function extractCep(): string
    {
        // Format A: 8 digits  |  Format B: 74.840-460
        if (preg_match('/\b(\d{2}\.\d{3}-\d{3})\b/', $this->page3, $m)) {
            return preg_replace('/\D/', '', $m[1]); // return as 8 digits
        }
        if (preg_match('/\b(\d{8})\b/', $this->page3, $m)) {
            return $m[1];
        }
        return '';
    }

    private function extractCidade(): string
    {
        if ($this->format === 'B') {
            // After formatted CEP (XX.XXX-XXX)
            if (preg_match('/\d{2}\.\d{3}-\d{3}\s+([A-ZÁÉÍÓÚÃÕÂÊÎ][A-ZÁÉÍÓÚÃÕÂÊÎ ]+?)\s+[A-ZÁÉÍÓÚÃÕÂÊÎ]{2,}/u', $this->page3, $m)) {
                return trim($m[1]);
            }
        }
        // Format A: after 8-digit plain CEP
        if (preg_match('/\b\d{8}\b\s+([A-Z][A-Z ]+?)\s+[A-Z]{2}\b/', $this->page3, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function extractUf(): string
    {
        if ($this->format === 'B') {
            // GOIANIA GOIAS → extract GOIAS then normalize
            if (preg_match('/\d{2}\.\d{3}-\d{3}\s+[A-ZÁÉÍÓÚÃÕÂÊÎ][A-ZÁÉÍÓÚÃÕÂÊÎ ]+?\s+([A-ZÁÉÍÓÚÃÕÂÊÎ]{2,})/u', $this->page3, $m)) {
                return $this->normalizeUf($m[1]);
            }
        }
        if (preg_match('/\b\d{8}\b\s+[A-Z][A-Z ]+?\s+([A-Z]{2})\b/', $this->page3, $m)) {
            return $m[1];
        }
        return '';
    }

    private function extractCelular(): string
    {
        if ($this->format === 'B') {
            // Format B: (62)999309430
            if (preg_match('/\((\d{2})\)(\d{8,9})/', $this->page3, $m)) {
                return $m[1] . $m[2];
            }
        }
        // Format A: digits after 2-letter UF
        if (preg_match('/\b[A-Z]{2}\b\s+(\d{10,11})\b/', $this->page3, $m)) {
            return $m[1];
        }
        return '';
    }

    private function extractEmail(): string
    {
        if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i', $this->page3, $m)) {
            return strtolower($m[1]);
        }
        return '';
    }

    private function extractPropostaNr(): string
    {
        if ($this->format === 'A') {
            if (preg_match('/\b(\d{6})\b\s+[A-Z][A-Z ]+\s+\d{2}\/\d{2}\/\d{4}/', $this->page3, $m)) {
                return $m[1];
            }
        }
        // Format B: no standard proposta number in this format
        return '';
    }

    private function extractResponsavel(): string
    {
        if ($this->format === 'A') {
            if (preg_match('/\b\d{6}\b\s+([A-Z][A-Z ]+?)\s+\d{2}\/\d{2}\/\d{4}/', $this->page3, $m)) {
                return trim($m[1]);
            }
        }
        // Format B: name comes after first phone (DD)XXXXXXXX and before CPF
        if (preg_match('/\(\d{2}\)\d{8,9}\s+([A-ZÁÉÍÓÚÃÕÂÊÎ][A-ZÁÉÍÓÚÃÕÂÊÎ ]+?)\s+\d{3}\.\d{3}\.\d{3}-\d{2}/u', $this->page3, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function extractDataVigencia(): string
    {
        if ($this->format === 'A') {
            if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $this->page3, $m)) {
                return $this->convertDate($m[1]);
            }
        }
        // Format B: DD MONTHNAME YYYY
        if (preg_match('/(\d{1,2})\s+(JANEIRO|FEVEREIRO|MAR[CÇ]O|ABRIL|MAIO|JUNHO|JULHO|AGOSTO|SETEMBRO|OUTUBRO|NOVEMBRO|DEZEMBRO)\s+(\d{4})/iu', $this->page3, $m)) {
            $month = self::MONTHS[mb_strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $m[2]))];
            return sprintf('%s-%s-%02d', $m[3], $month, (int)$m[1]);
        }
        return '';
    }

    private function extractVencimentoDia(): int
    {
        if ($this->format === 'A') {
            if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2})\s*$/', $this->page3, $m)) {
                return (int) $m[2];
            }
        }
        // Format B: single digit/number immediately before the text date
        if (preg_match('/(\d{1,2})\s+\d{1,2}\s+(?:JANEIRO|FEVEREIRO|MAR[CÇ]O|ABRIL|MAIO|JUNHO|JULHO|AGOSTO|SETEMBRO|OUTUBRO|NOVEMBRO|DEZEMBRO)/iu', $this->page3, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    private function buildDataBoleto(): string
    {
        $vigencia = $this->extractDataVigencia();
        $dia      = $this->extractVencimentoDia();
        if (!$vigencia || !$dia) return $vigencia;

        $parts = explode('-', $vigencia);
        if (count($parts) !== 3) return $vigencia;

        $lastDay = cal_days_in_month(CAL_GREGORIAN, (int)$parts[1], (int)$parts[0]);
        $dia     = min($dia, $lastDay);

        return sprintf('%s-%s-%02d', $parts[0], $parts[1], $dia);
    }

    private function extractValorSaude(): string
    {
        $vals = $this->extractMoneyValues();
        return $vals[0] ?? '0.00';
    }

    private function extractValorOdonto(): string
    {
        $vals = $this->extractMoneyValues();
        return $vals[1] ?? '0.00';
    }

    private function extractTaxaAdesao(): string
    {
        $vals = $this->extractMoneyValues();
        return $vals[2] ?? '0.00';
    }

    private function extractValorTotal(): string
    {
        $vals = $this->extractMoneyValues();
        return $vals[3] ?? '0.00';
    }

    private function extractMoneyValues(): array
    {
        if ($this->format === 'A') {
            // Format A: R$ followed by non-breaking or regular space then number
            preg_match_all('/R\$[^\d]*([\d]+[\d\.]*,\d{2})/u', $this->page3, $m);
            return array_map([$this, 'parseDecimal'], $m[1] ?? []);
        }

        // Format B: 4 decimal values appear consecutively without R$ prefix
        // Pattern: find sequence of N,NN values separated by spaces
        // They appear as: 723,98 0,00 40,00 763,98
        if (preg_match_all('/\b(\d{1,7},\d{2})\b/', $this->page3, $m)) {
            $raw = $m[1];
            // Remove duplicates, keep the first 4 occurrences that look like money
            $vals = [];
            foreach ($raw as $v) {
                $vals[] = $this->parseDecimal($v);
                if (count($vals) >= 4) break;
            }
            return $vals;
        }
        return [];
    }

    // ─── Page 3/4 plan extractions ────────────────────────────

    private function extractNomeComercialSaude(): string
    {
        if ($this->format === 'A') {
            if (preg_match('/Nome Comercial\s+(.+)/u', $this->page4, $m)) {
                return trim($m[1]);
            }
        }

        // Format B: plan name appears after first ANS code in page 3
        // Detect which ANS code the beneficiaries are on, then find matching plan
        $ansFromBenef = $this->extractAnsBeneficiarios();
        foreach ([$this->page3, $this->page4] as $page) {
            if (!empty($ansFromBenef)) {
                // Find the plan name that follows this ANS code
                $ansEscaped = preg_quote($ansFromBenef, '/');
                if (preg_match('/' . $ansEscaped . '\s+(NOSSO PLANO[^\n]+)/u', $page, $m)) {
                    return trim($m[1]);
                }
            }
            // Fallback: first NOSSO PLANO occurrence
            if (preg_match('/(NOSSO PLANO[^\n]+)/u', $page, $m)) {
                return trim($m[1]);
            }
        }
        return '';
    }

    private function extractCodigoComercialSaude(): string
    {
        if ($this->format === 'A') {
            if (preg_match('/Cód\. Comercial\s+(\d+)/u', $this->page4, $m)) {
                return $m[1];
            }
        }
        // Format B: Código Interno is '-' (empty)
        return '';
    }

    private function extractCodigoAnsSaude(): string
    {
        if ($this->format === 'A') {
            if (preg_match('/Cód\. ANS - Saúde\s+(\d+)/u', $this->page4, $m)) {
                return $m[1];
            }
        }
        // Format B: ANS code in format 487.823/20-0 → strip to digits
        if (preg_match('/(\d{3}\.\d{3}\/\d{2}-\d{1})/', $this->page3, $m)) {
            return preg_replace('/\D/', '', $m[1]);
        }
        return '';
    }

    private function extractCodigoComercialOdonto(): string
    {
        if ($this->format === 'A') {
            $matches = [];
            preg_match_all('/Cód\. Comercial\s+(\d+)/u', $this->page4, $matches);
            return $matches[1][1] ?? '';
        }
        return '';
    }

    private function extractCodigoAnsOdonto(): string
    {
        if ($this->format === 'A') {
            $matches = [];
            preg_match_all('/Cód\. ANS - Saúde\s+(\d+)/u', $this->page4, $matches);
            return $matches[1][1] ?? '';
        }
        return '';
    }

    private function extractVidas(): int
    {
        // Format A: explicit Nº de Vidas N in page 4
        foreach ([$this->page4, $this->page3] as $page) {
            if (preg_match('/Nº de Vidas\s+(\d+)/u', $page, $m)) {
                return (int) $m[1];
            }
        }
        // Format B: count beneficiaries from beneficiary page
        if ($this->format === 'B') {
            $benef = $this->extractBeneficiarios();
            return count($benef) ?: 0;
        }
        return 0;
    }

    private function extractAreaAtuacao(): string
    {
        if ($this->format === 'A') {
            if (preg_match('/Área atuação\s*\n([^\n]+)/u', $this->page4, $m)) {
                return trim($m[1]);
            }
        }
        // Format B: cities listed under "Grupo de municípios"
        foreach ([$this->page3, $this->page4] as $page) {
            if (preg_match('/Grupo de munic[^\n]*\n([^\n]+)/u', $page, $m)) {
                return trim($m[1]);
            }
        }
        return '';
    }

    private function extractPrimeiraCidade(): string
    {
        $area = $this->extractAreaAtuacao();
        if (empty($area)) return '';

        if ($this->format === 'A') {
            // "GOIANIA/GO, ANAPOLIS/GO, ..."
            $first = explode(',', $area)[0];
            $parts = explode('/', trim($first));
            return trim($parts[0]);
        }

        // Format B: "Goiânia, Anápolis, Aparecida de Goiânia, ..."
        $first = trim(explode(',', $area)[0]);
        return $first;
    }

    // ─── Page 5 extractions ────────────────────────────────────

    private function extractNomeVendedor(): string
    {
        if (preg_match('/\b([A-Z]+(?:\s+[A-Z]+){1,4})\s+(\d{6,7})\s+[A-Z]/u', $this->page5, $m)) {
            $words = array_values(array_filter(explode(' ', trim($m[1]))));
            $count = count($words);
            $slice = array_slice($words, max(0, $count - 4));
            return implode(' ', $slice);
        }
        return '';
    }

    private function extractCodigoVendedor(): string
    {
        if (preg_match('/\b([A-Z]+(?:\s+[A-Z]+){1,4})\s+(\d{6,7})\s+[A-Z]/u', $this->page5, $m)) {
            return $m[2];
        }
        return '';
    }

    private function extractCodigoCorretora(): string
    {
        if (preg_match('/[A-Z][A-Z ]{5,}?\s+\d{6,7}\s+([A-Z][A-Z ]{5,}?)\s+(\d{4,5})\s+\d{2}\s+\d{2}\s+\d{4}/u', $this->page5, $m)) {
            return $m[2];
        }
        return '';
    }

    // ─── Beneficiários ─────────────────────────────────────────

    private function findBenefPage(array $pages, int $total): string
    {
        $start = max(0, $total - 15);
        for ($i = $start; $i < $total; $i++) {
            $text = $pages[$i]->getText();
            if (strpos($text, 'Titular / Dependente') !== false
                || strpos($text, 'Dt Nascimento') !== false
                || (preg_match('/\d{3}\.\d{3}\.\d{3}-\d{2}\s+[A-Z]/u', $text)
                    && strpos($text, 'SIM') !== false)) {
                return $text;
            }
        }
        return '';
    }

    private function extractBeneficiarios(): array
    {
        if (empty($this->pageBenef)) return [];

        if ($this->format === 'A') {
            return $this->extractBeneficiariosFormatA();
        }
        return $this->extractBeneficiariosFormatB();
    }

    private function extractBeneficiariosFormatA(): array
    {
        $beneficiarios = [];
        // CPF + nome + T/D + data + estado civil + nome mae + codigo ANS + R$ valor + N
        $pattern = '/(\d{3}\.\d{3}\.\d{3}-\d{2})([A-ZÁÉÍÓÚ\s]+?)\s+(T|D)\s+(\d{2}\/\d{2}\/\d{4})\s+\w+\s+[A-ZÁÉÍÓÚ\s]+?\s+\d{9}\s+R\$\s*([\d,\.]+)N/u';
        preg_match_all($pattern, $this->pageBenef, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $beneficiarios[] = [
                'cpf'             => trim($m[1]),
                'nome'            => trim($m[2]),
                'tipo'            => $m[3],
                'data_nascimento' => $this->convertDate($m[4]),
                'valor'           => $this->parseDecimal($m[5]),
            ];
        }
        return $beneficiarios;
    }

    private function extractBeneficiariosFormatB(): array
    {
        $beneficiarios = [];
        // Format B: CPF nome data_nasc estado_civil nome_mae ANS_code valor SIM
        // e.g. 028.147.511-31 MARCELA GARCIA REIS 10/05/1990 C SANDRA SARTIN PINTO REIS 487.823/20-0 361,99 SIM
        $pattern = '/(\d{3}\.\d{3}\.\d{3}-\d{2})\s+([A-ZÁÉÍÓÚÃÕÂÊÎ][A-ZÁÉÍÓÚÃÕÂÊÎ\s]+?)\s+(\d{2}\/\d{2}\/\d{4})\s+[SCVDO]\s+[A-ZÁÉÍÓÚÃÕÂÊÎ\s]+?\s+\d{3}\.\d{3}\/\d{2}-\d\s+([\d,\.]+)\s+SIM/u';
        preg_match_all($pattern, $this->pageBenef, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $beneficiarios[] = [
                'cpf'             => trim($m[1]),
                'nome'            => trim($m[2]),
                'tipo'            => 'T', // Format B has no T/D distinction
                'data_nascimento' => $this->convertDate($m[3]),
                'valor'           => $this->parseDecimal($m[4]),
            ];
        }
        return $beneficiarios;
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function extractAnsBeneficiarios(): string
    {
        if (preg_match('/\d{3}\.\d{3}\.\d{3}-\d{2}\s+[A-ZÁÉÍÓÚÃÕÂÊÎ\s]+?\s+\d{2}\/\d{2}\/\d{4}.*?(\d{3}\.\d{3}\/\d{2}-\d)/us', $this->pageBenef, $m)) {
            return $m[1];
        }
        return '';
    }

    private function normalizeUf(string $uf): string
    {
        if (strlen(trim($uf)) === 2) return strtoupper(trim($uf));
        $key = strtoupper(preg_replace('/\s+/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $uf)));
        return self::UF_MAP[$key] ?? strtoupper(trim($uf));
    }

    private function normalizeText(string $text): string
    {
        // Replace non-breaking space (U+00A0, UTF-8: C2 A0) with regular space
        return str_replace("\xc2\xa0", ' ', $text);
    }

    private function convertDate(string $date): string
    {
        $parts = explode('/', $date);
        return count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : $date;
    }

    private function parseDecimal(string $v): string
    {
        return number_format((float) str_replace(['.', ','], ['', '.'], $v), 2, '.', '');
    }
}
