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

    /**
     * Valores dos campos de formulário achatados (XObjects/Form), na ordem dos
     * objetos do PDF. Em alguns PDFs os dados preenchidos vivem nesses XObjects
     * e o getText() da página os despeja colados no fim — aqui ficam separados.
     */
    private array $formFields = [];

    private const MONTHS = [
        'JANEIRO'=>'01','FEVEREIRO'=>'02','MARCO'=>'03','MARÇO'=>'03',
        'ABRIL'=>'04','MAIO'=>'05','JUNHO'=>'06','JULHO'=>'07',
        'AGOSTO'=>'08','SETEMBRO'=>'09','OUTUBRO'=>'10',
        'NOVEMBRO'=>'11','DEZEMBRO'=>'12',
    ];

    private const UF_REGEX = '(AC|AL|AP|AM|BA|CE|DF|ES|GO|MA|MT|MS|MG|PA|PB|PR|PE|PI|RJ|RN|RS|RO|RR|SC|SP|SE|TO)';

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

        // Layout "CONTRATAÇÃO SUPER SIMPLES" (versão 2026.06, assinatura
        // eletrônica): estrutura própria — dados na página 2 ("Nome do plano"),
        // beneficiários e assinatura no bloco final. Tratado num caminho
        // separado. Propostas antigas também dizem "SUPER SIMPLES" na capa,
        // por isso o discriminador é a página 2.
        $page2 = $total > 1 ? $this->normalizeText($pages[1]->getText()) : '';
        if (stripos($page2, 'Nome do plano') !== false && stripos($page2, 'Tot/ plano') !== false) {
            return $this->parseSuperSimples($pages, $total);
        }

        $this->page3    = $this->normalizeText($total > 2 ? $pages[2]->getText() : '');
        $this->page4    = $this->normalizeText($total > 3 ? $pages[3]->getText() : '');
        $this->page5    = $this->normalizeText($total > 4 ? $pages[4]->getText() : '');
        $this->pageBenef = $this->normalizeText($this->findBenefPage($pages, $total));
        $this->formFields = $this->extractFormFields($pdf);

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

    // ─── Layout SUPER SIMPLES (2026.06, assinatura eletrônica) ──

    private function parseSuperSimples(array $pages, int $total): array
    {
        // Página 2: dados da empresa, responsável, plano e valores
        $p2 = $this->normalizeText($total > 1 ? $pages[1]->getText() : '');

        // Bloco final: beneficiários e página de assinatura (vendedor + data)
        $benef = '';
        $assin = '';
        for ($i = 2; $i < $total; $i++) {
            $t = $this->normalizeText($pages[$i]->getText());
            if ($benef === '' && stripos($t, 'BENEFICIÁRIOS(AS) COM TERMO') !== false
                && preg_match('/\d{3}\.\d{3}\.\d{3}-\d{2}\t/', $t)) {
                $benef = $t;
            }
            if (stripos($t, 'Intermediário entre a operadora') !== false) {
                $assin = $t;
            }
        }

        $dados = [
            'proposta_nr' => '', 'cnpj' => '', 'razao_social' => '', 'cidade' => '',
            'uf' => '', 'cep' => '', 'celular' => '', 'email' => '', 'responsavel' => '',
            'data_vigencia' => '', 'vencimento_dia' => 0, 'data_boleto' => '',
            'plano_nome_comercial' => '', 'codigo_saude' => '', 'codigo_ans_saude' => '',
            'codigo_odonto' => '', 'codigo_ans_odonto' => '', 'vidas' => 0,
            'area_atuacao' => '', 'tabela_cidade' => '', 'codigo_vendedor' => '',
            'nome_vendedor' => '', 'codigo_corretora' => '',
            'valor_plano_saude' => '0.00', 'valor_plano_odonto' => '0.00',
            'taxa_adesao' => '0.00', 'valor_total' => '0.00', 'beneficiarios' => [],
        ];

        // CNPJ (14 dígitos corridos) seguido da razão social na mesma linha
        if (preg_match('/(\d{14})\t([^\n]+)/u', $p2, $m)) {
            $d = $m[1];
            $dados['cnpj'] = substr($d,0,2).'.'.substr($d,2,3).'.'.substr($d,5,3).'/'.substr($d,8,4).'-'.substr($d,12,2);
            $dados['razao_social'] = trim($m[2]);
        }

        if (preg_match('/\b(\d{5}-\d{3})\b/', $p2, $m)) {
            $dados['cep'] = preg_replace('/\D/', '', $m[1]);
        }
        if (preg_match('/\n([A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]+?)\t' . self::UF_REGEX . '\n/u', $p2, $m)) {
            $dados['cidade'] = trim($m[1]);
            $dados['uf']     = $m[2];
            $dados['tabela_cidade'] = trim($m[1]);
        }

        // Responsável: linha de nome seguida do CPF
        if (preg_match('/\n([A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]{5,})\n(\d{3}\.\d{3}\.\d{3}-\d{2})\n/u', $p2, $m)) {
            $dados['responsavel'] = trim($m[1]);
        }

        // E-mail e telefone vêm colados: "email@x.com(62)99418-5543"
        if (preg_match('/([a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,})/i', $p2, $m)) {
            $dados['email'] = strtolower($m[1]);
        }
        if (preg_match('/\((\d{2})\)\s?(\d{4,5})-?(\d{4})/', $p2, $m)) {
            $dados['celular'] = $m[1] . $m[2] . $m[3];
        }

        if (preg_match('/Nome do plano\s*\n([^\n]+)/u', $p2, $m)) {
            $dados['plano_nome_comercial'] = trim($m[1]);
        }
        if (preg_match('/Reg\.\s*ANS\s*\n(\d{6,12})/u', $p2, $m)) {
            $dados['codigo_ans_saude'] = $m[1];
        }
        if (preg_match('/Vidas\s*\n(\d+)/u', $p2, $m)) {
            $dados['vidas'] = (int) $m[1];
        }
        // Valor único (saúde+odonto integrados no plano)
        if (preg_match('/Tot\/\s*plano\s*\n([\d\.]+,\d{2})/u', $p2, $m)) {
            $dados['valor_plano_saude'] = $this->parseDecimal($m[1]);
            $dados['valor_total']       = $this->parseDecimal($m[1]);
        }
        if (preg_match('/(\d{5,8})\s*\nAssinatura eletr/u', $p2, $m)) {
            $dados['proposta_nr'] = $m[1];
        }

        // Data de assinatura (ddmmaaaa na página de assinatura) — a vigência
        // real fica em branco neste layout; a backoffice ajusta no formulário
        foreach ([$assin, $p2] as $texto) {
            if ($texto !== '' && preg_match_all('/\b(\d{2})(\d{2})(20\d{2})\b/', $texto, $mm, PREG_SET_ORDER)) {
                foreach ($mm as $m) {
                    if ((int)$m[1] >= 1 && (int)$m[1] <= 31 && (int)$m[2] >= 1 && (int)$m[2] <= 12) {
                        $dados['data_vigencia'] = "{$m[3]}-{$m[2]}-{$m[1]}";
                        $dados['data_boleto']   = $dados['data_vigencia'];
                        break 2;
                    }
                }
            }
        }

        // Vendedor(a): na página de assinatura, primeiro nome em caixa alta
        // que não seja a razão social nem o responsável
        if ($assin !== '' && preg_match_all('/^([A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]{8,})$/mu', $assin, $mm)) {
            foreach ($mm[1] as $nome) {
                $nome = trim($nome);
                if ($nome !== $dados['razao_social'] && $nome !== $dados['responsavel']) {
                    $dados['nome_vendedor'] = $nome;
                    break;
                }
            }
        }

        // Beneficiários: "CPF <tab> 251,56NOME COMPLETO 30/01/2004..."
        if ($benef !== '' && preg_match_all(
            '/(\d{3}\.\d{3}\.\d{3}-\d{2})\t([\d\.]+,\d{2})([A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]+?) (\d{2}\/\d{2}\/\d{4})/u',
            $benef, $mm, PREG_SET_ORDER
        )) {
            foreach ($mm as $m) {
                $dados['beneficiarios'][] = [
                    'cpf'             => $m[1],
                    'nome'            => trim($m[3]),
                    'tipo'            => 'T',
                    'data_nascimento' => $this->convertDate($m[4]),
                    'valor'           => $this->parseDecimal($m[2]),
                ];
            }
            if ($dados['vidas'] === 0) {
                $dados['vidas'] = count($dados['beneficiarios']);
            }
        }

        return $dados;
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
        // CAEPF (produtor rural / CPF-base): 341.990.980/007-77
        if (preg_match('/(\d{3}\.\d{3}\.\d{3}\/\d{3}-\d{2})/', $this->page3, $m)) {
            return $m[1];
        }
        // Ultimo recurso: CPF simples
        if (preg_match('/(\d{3}\.\d{3}\.\d{3}-\d{2})/', $this->page3, $m)) {
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
        // CAEPF: nome vem apos o CAEPF, endereco pode ser rodovia (ex.: "GO 139 KM 30")
        if (preg_match(
            '/\d{3}\.\d{3}\.\d{3}\/\d{3}-\d{2}\s+([A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]+?)\s+(?:' . $keywords . '|[A-Z]{2}\s?\d|\d)/u',
            $this->page3, $m
        )) {
            return trim($m[1]);
        }
        // Fallback (layout com campos achatados): o campo cujo texto aparece
        // logo apos o CNPJ no texto da pagina e a razao social — escolhe o
        // match mais longo (o mesmo texto pode existir truncado noutro campo)
        if (preg_match('/(?:\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{3}\.\d{3}\.\d{3}\/\d{3}-\d{2})\s+(.{5,120})/u', $this->page3, $m)) {
            $resto = trim($m[1]);
            $melhor = '';
            foreach ($this->formFields as $campo) {
                if (mb_strlen($campo) >= 5 && mb_strlen($campo) > mb_strlen($melhor)
                    && str_starts_with($resto, $campo)) {
                    $melhor = $campo;
                }
            }
            if ($melhor !== '') {
                return $melhor;
            }
        }
        // Fallback: responsavel (no CAEPF a razao social e a propria pessoa)
        $resp = $this->extractResponsavel();
        if ($resp !== '') {
            return $resp;
        }
        return '';
    }

    private function extractCep(): string
    {
        // Format A: 8 digits  |  Format B: 74.840-460
        if (preg_match('/\b(\d{2}\.\d{3}-\d{3})\b/', $this->page3, $m)) {
            return preg_replace('/\D/', '', $m[1]); // return as 8 digits
        }
        // Prefere o CEP seguido de cidade + UF valida (evita pegar fragmentos de CNPJ)
        if (preg_match('/\b(\d{8})\b\s+[A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]+?\s+' . self::UF_REGEX . '\b/u', $this->page3, $m)) {
            return $m[1];
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
        // Format A: after 8-digit plain CEP, cidade termina numa UF VALIDA seguida
        // de telefone/numero/fim (evita cortar "SAO MIGUEL DO PASSA QUATRO" em "DO")
        if (preg_match('/\b\d{8}\b\s+([A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]+?)\s+' . self::UF_REGEX . '\s*(?=\(|\d|$)/u', $this->page3, $m)) {
            return trim($m[1]);
        }
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
        if (preg_match('/\b\d{8}\b\s+[A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]+?\s+' . self::UF_REGEX . '\s*(?=\(|\d|$)/u', $this->page3, $m)) {
            return $m[1];
        }
        if (preg_match('/\b\d{8}\b\s+[A-Z][A-Z ]+?\s+([A-Z]{2})\b/', $this->page3, $m)) {
            return $m[1];
        }
        return '';
    }

    private function extractCelular(): string
    {
        if ($this->format === 'B') {
            // Format B: (62)999309430 ou (62)99982-7148
            if (preg_match('/\((\d{2})\)\s?(\d{4,5})-?(\d{4})/', $this->page3, $m)) {
                return $m[1] . $m[2] . $m[3];
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
        // Hibrido: numero de 6 digitos entre os valores e a data por extenso
        // ex.: "R$ 1.388,68 980352 4 4 3 09 JULHO 2026"
        if (preg_match('/,\d{2}\s+(\d{6})\b(?:\s+\d{1,2}){0,4}\s+\d{1,2}\s+(?:JANEIRO|FEVEREIRO|MAR[CÇ]O|ABRIL|MAIO|JUNHO|JULHO|AGOSTO|SETEMBRO|OUTUBRO|NOVEMBRO|DEZEMBRO)/iu', $this->page3, $m)) {
            return $m[1];
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
        if (preg_match('/\(\d{2}\)\s?\d{4,5}-?\d{4}\s+([A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]+?)\s+\d{3}\.\d{3}\.\d{3}-\d{2}/u', $this->page3, $m)) {
            return trim($m[1]);
        }
        // Layout com campos achatados: o card "Dados do responsavel" tem o CPF
        // mas o nome so aparece junto a assinatura — pega o campo de nome
        // vizinho ao campo que contem o CPF do responsavel
        if (preg_match('/(\d{3}\.\d{3}\.\d{3}-\d{2})/', $this->page3, $m)) {
            $cpfResp = $m[1];
            foreach ($this->formFields as $i => $campo) {
                if ($campo !== $cpfResp) continue;
                foreach ([$i - 1, $i + 1] as $j) {
                    $viz = $this->formFields[$j] ?? '';
                    if (preg_match('/^[A-ZÁÉÍÓÚÃÕÂÊÎÇ][A-ZÁÉÍÓÚÃÕÂÊÎÇ ]{5,}$/u', $viz)
                        && count(preg_split('/\s+/', trim($viz))) >= 2) {
                        return trim($viz);
                    }
                }
            }
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
        // Valores prefixados com R$ tem prioridade em qualquer formato
        // (captura milhar: R$ 1.258,93 e nao apenas 258,93)
        if (preg_match_all('/R\$[^\d]*(\d{1,3}(?:\.\d{3})*,\d{2})/u', $this->page3, $m) && count($m[1]) >= 2) {
            return array_map([$this, 'parseDecimal'], $m[1]);
        }

        if ($this->format === 'A') {
            preg_match_all('/R\$[^\d]*([\d]+[\d\.]*,\d{2})/u', $this->page3, $m);
            return array_map([$this, 'parseDecimal'], $m[1] ?? []);
        }

        // Format B: 4 decimal values appear consecutively without R$ prefix
        // They appear as: 723,98 0,00 40,00 763,98 (com ou sem milhar: 1.258,93)
        if (preg_match_all('/(?<![\d.])(\d{1,3}(?:\.\d{3})*,\d{2})\b/', $this->page3, $m)) {
            $vals = [];
            foreach ($m[1] as $v) {
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
        // Prefere o ANS que aparece na lista de beneficiarios (plano contratado)
        $ansBenef = $this->extractAnsBeneficiarios();
        if ($ansBenef !== '') {
            return preg_replace('/\D/', '', $ansBenef);
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

        // O layout da pagina de beneficiarios independe do formato da pagina 3
        // (ha PDFs hibridos) — tenta os dois e usa o que encontrar registros
        $a = $this->extractBeneficiariosFormatA();
        $b = $this->extractBeneficiariosFormatB();
        if ($this->format === 'A') {
            return count($a) ? $a : $b;
        }
        return count($b) ? $b : $a;
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
        // Layout com ANS em digitos corridos (ex.: 487815209) — devolve formatado
        if (preg_match('/\d{3}\.\d{3}\.\d{3}-\d{2}.{0,20}?[A-ZÁÉÍÓÚÃÕÂÊÎ\s]+?\s+[TD]\s+\d{2}\/\d{2}\/\d{4}.*?\b(\d{9})\b/us', $this->pageBenef, $m)) {
            $d = $m[1];
            return substr($d, 0, 3) . '.' . substr($d, 3, 3) . '/' . substr($d, 6, 2) . '-' . substr($d, 8, 1);
        }
        return '';
    }

    private function extractFormFields($pdf): array
    {
        $campos = [];
        try {
            foreach ($pdf->getObjects() as $id => $obj) {
                $h = $obj->getHeader();
                if (!$h) continue;
                $st = $h->get('Subtype');
                if (!$st || (string) $st->getContent() !== 'Form') continue;
                try {
                    $t = trim($this->normalizeText($obj->getText()));
                } catch (\Throwable $e) {
                    continue;
                }
                if ($t === '') continue;
                // Ordena pela numeracao do objeto (segue a ordem dos campos no PDF)
                $num = (int) explode('_', (string) $id)[0];
                $campos[$num] = $t;
            }
        } catch (\Throwable $e) {
            return [];
        }
        ksort($campos);
        return array_values($campos);
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
