<?php

namespace App\Services\PdfParser;

use Smalot\PdfParser\Parser;

class AlterPdfParser
{
    private string $text;

    public function parse(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);

        // Usa apenas as primeiras 3 páginas (dados do contrato)
        $pages = $pdf->getPages();
        $parts = [];
        for ($i = 0; $i < min(3, count($pages)); $i++) {
            $parts[] = $pages[$i]->getText();
        }
        $this->text = implode("\n", $parts);

        return [
            'codigo_externo'  => $this->extractCodigoExterno(),
            'data_vigencia'   => $this->extractDataVigencia(),
            'data_boleto'     => $this->extractDataAdmissao(),
            'vendedor_cpf'    => $this->extractVendedorCpf(),
            'vendedor_nome'   => $this->splitWords($this->extractVendedorNome()),
            'administradora'  => 'Alter',
            'entidade'        => $this->extractEntidade(),
            'titular'         => $this->extractTitular(),
            'plano'           => $this->extractPlano(),
            'valor_plano'     => $this->extractValorTotal(),
            'valor_adesao'    => $this->extractValorTotal(),
            'dependentes'     => $this->extractDependentes(),
        ];
    }

    // ─── Nº do contrato ──────────────────────────────────────────
    // Texto real: "8406058Nº "  →  número vem ANTES do "Nº"
    private function extractCodigoExterno(): string
    {
        if (preg_match('/(\d{6,10})\s*N[ºo°]/u', $this->text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ─── Data de admissão (usada como data_boleto) ────────────────
    // Texto real: "DatadeAdmissão\n11/05/2026"
    private function extractDataAdmissao(): string
    {
        if (preg_match('/DatadeAdmiss[aã]o\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            return $this->convertDate($m[1]);
        }
        if (preg_match('/Data\s*de\s*Admiss[aã]o\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            return $this->convertDate($m[1]);
        }
        // Fallback: usa a data de vigência
        return $this->extractDataVigencia();
    }

    // ─── Data de vigência ─────────────────────────────────────────
    // Texto real: "IníciodaVigência\n10/06/2026"
    private function extractDataVigencia(): string
    {
        // Label compacta (sem espaços) seguida de nova linha e data
        if (preg_match('/Vig[êe]ncia\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            return $this->convertDate($m[1]);
        }
        // Fallback: qualquer data dd/mm/yyyy após Início/Vigência
        if (preg_match('/Vig[êe]ncia[^\d]*(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            return $this->convertDate($m[1]);
        }
        return '';
    }

    // ─── CPF do vendedor ─────────────────────────────────────────
    // Texto real: "CPFdoVendedor\n896.190.211-34"
    private function extractVendedorCpf(): string
    {
        if (preg_match('/CPFdoVendedor\s*\n\s*([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})/ui', $this->text, $m)) {
            return $m[1];
        }
        // Fallback com possíveis variações de formatação
        if (preg_match('/CPF\s*do\s*Vendedor\s*\n\s*([\d]{3}[\.\s][\d]{3}[\.\s][\d]{3}[\-\s][\d]{2})/ui', $this->text, $m)) {
            return $this->normalizeCpf($m[1]);
        }
        return '';
    }

    // ─── Nome do vendedor ─────────────────────────────────────────
    // Texto real: "NomedoVendedor\nMarisaAraújo"
    private function extractVendedorNome(): string
    {
        if (preg_match('/NomedoVendedor\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/Nome\s*do\s*Vendedor\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ─── Entidade ─────────────────────────────────────────────────
    // Texto real: "Entidade:\nFETRABRAS-FETRACESP"
    private function extractEntidade(): string
    {
        if (preg_match('/Entidade\s*[:.]?\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ─── Titular ─────────────────────────────────────────────────
    private function extractTitular(): array
    {
        $t = [
            'nome'            => '',
            'cpf'             => '',
            'data_nascimento' => '',
            'celular'         => '',
            'email'           => '',
            'cep'             => '',
            'rua'             => '',
            'bairro'          => '',
            'cidade'          => '',
            'uf'              => '',
        ];

        // Nome: "Nome( Completo)\nDeborahRodriguesDido"
        if (preg_match('/Nome\s*\(\s*Completo\s*\)\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            $t['nome'] = $this->splitWords(trim($m[1]));
        }

        // CPF: "CPF\n081.178.551-35"
        // Pega o primeiro CPF da seção do titular (antes da seção de dependentes)
        $secaoTitular = $this->text;
        if (preg_match('/1\s*[–-]\s*PROPONENTE\s*TITULAR([\s\S]*?)2\s*[–-]\s*DEPENDENTES/ui', $this->text, $ms)) {
            $secaoTitular = $ms[1];
        }
        if (preg_match('/\bCPF\b\s*\n\s*([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})/ui', $secaoTitular, $m)) {
            $t['cpf'] = $m[1];
        }

        // Data de Nascimento: "DatadeNascimento\n27/02/2002"
        if (preg_match('/DatadeNascimento\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $secaoTitular, $m)) {
            $t['data_nascimento'] = $this->convertDate($m[1]);
        } elseif (preg_match('/Data\s*de\s*Nascimento\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $secaoTitular, $m)) {
            $t['data_nascimento'] = $this->convertDate($m[1]);
        }

        // Telefone: "Telefone1\n(62)99197-3305"
        if (preg_match('/Telefone1?\s*\n\s*([^\n\t]+)/ui', $secaoTitular, $m)) {
            $raw = trim($m[1]);
            // Normaliza "(62)99197-3305" → "(62) 99197-3305"
            $t['celular'] = preg_replace('/\)(\d)/', ') $1', $raw);
        }

        // E-mail: "E-mail\nglicerio.carlos@gmail.com"
        if (preg_match('/E-?mail\s*\n\s*([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/ui', $secaoTitular, $m)) {
            $t['email'] = strtolower(trim($m[1]));
        } elseif (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/u', $secaoTitular, $m)) {
            $t['email'] = strtolower(trim($m[1]));
        }

        // CEP: "CEP\n75920-000"
        if (preg_match('/\bCEP\b\s*\n\s*([\d]{5}-[\d]{3})/ui', $secaoTitular, $m)) {
            $t['cep'] = $m[1];
        }

        // Endereço / Rua: "EndereçoResidencial\nRuaJoãodebarro"
        if (preg_match('/Endere[cç]o\s*(?:Residencial)?\s*\n\s*([^\n]+)/ui', $secaoTitular, $m)) {
            $t['rua'] = $this->splitWords(trim($m[1]));
        }

        // Bairro: "Bairro\nParqueresidencialIsaura"
        if (preg_match('/\bBairro\b\s*\n\s*([^\n]+)/ui', $secaoTitular, $m)) {
            $t['bairro'] = $this->splitWords(trim($m[1]));
        }

        // Cidade: "Cidade\nSantaHelenadeGoiás"
        if (preg_match('/\bCidade\b\s*\n\s*([^\n]+)/ui', $secaoTitular, $m)) {
            $t['cidade'] = $this->splitWords(trim($m[1]));
        }

        // UF: "UF\nGO"
        if (preg_match('/\bUF\b\s*\n\s*([A-Z]{2})\b/u', $secaoTitular, $m)) {
            $t['uf'] = $m[1];
        }

        return $t;
    }

    // ─── Plano ───────────────────────────────────────────────────
    private function extractPlano(): array
    {
        $plano = [
            'nome'           => '',
            'codigo_ans'     => '',
            'acomodacao'     => 'ENFERMARIA',
            'coparticipacao' => false,
            'odonto'         => false,
        ];

        // Nome do plano: "Plano\nINTEGRADO411E-COPARTTOTALENFCENTROOESTE"
        if (preg_match('/\bPlano\b\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            $plano['nome'] = trim($m[1]);
        }

        // Código ANS: "CódigoANS\n482993190"
        if (preg_match('/C[oó]digoANS\s*\n\s*(\d{6,12})/ui', $this->text, $m)) {
            $plano['codigo_ans'] = $m[1];
        } elseif (preg_match('/C[oó]digo\s*ANS\s*\n\s*(\d{6,12})/ui', $this->text, $m)) {
            $plano['codigo_ans'] = $m[1];
        }

        // Acomodação: "Acomodação\nENFERMARIA"
        if (preg_match('/Acomoda[cç][aã]o\s*\n\s*(ENFERMARIA|APARTAMENTO)/ui', $this->text, $m)) {
            $plano['acomodacao'] = strtoupper($m[1]);
        }

        // Coparticipação: "FatorModerador–Coparticipação\nSIM"
        if (preg_match('/FatorModerador[^\n]*\n\s*(SIM|N[ÃA]O)/ui', $this->text, $m)) {
            $plano['coparticipacao'] = strtoupper(trim($m[1])) === 'SIM';
        } elseif (preg_match('/COPART/ui', $this->text)) {
            $plano['coparticipacao'] = true;
        }

        // Odonto
        if (preg_match('/ODONTO/ui', $this->text)) {
            $plano['odonto'] = true;
        }

        return $plano;
    }

    // ─── Valor total ─────────────────────────────────────────────
    // Texto real: "VALORTOTALEM R$TITULAR+DEPENDENTES\n292,34"
    private function extractValorTotal(): string
    {
        // Padrão principal: valor logo após "DEPENDENTES" (linha seguinte)
        if (preg_match('/DEPENDENTES\s*\n\s*([\d\.]+,\d{2})/ui', $this->text, $m)) {
            return $this->parseDecimal($m[1]);
        }
        // Fallback: qualquer valor após DEPENDENTES na mesma ou próxima linha
        if (preg_match('/DEPENDENTES[^\d\n]*([\d\.]+,\d{2})/ui', $this->text, $m)) {
            return $this->parseDecimal($m[1]);
        }
        // Último fallback: VALORTOTAL seguido de valor
        if (preg_match('/VALORTOTAL[^\d]*([\d\.]+,\d{2})/ui', $this->text, $m)) {
            return $this->parseDecimal($m[1]);
        }
        return '0.00';
    }

    // ─── Dependentes ─────────────────────────────────────────────
    private function extractDependentes(): array
    {
        $dependentes = [];

        // Extrai a seção de dependentes
        $secao = '';
        if (preg_match('/2\s*[–-]\s*DEPENDENTES([\s\S]*?)(?:3\s*[–-]|PLANO\s*PRETENDIDO|\z)/ui', $this->text, $m)) {
            $secao = $m[1];
        }
        if (empty(trim($secao))) return $dependentes;

        // Cada bloco de dependente: nome na linha após "Nome( Completo)"
        // Aqui os slots podem estar em branco — captura CPFs presentes
        if (preg_match_all('/([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})/u', $secao, $cpfMatches)) {
            foreach ($cpfMatches[1] as $cpf) {
                $dependentes[] = ['nome' => '', 'cpf' => $cpf];
            }
        }

        return $dependentes;
    }

    // ─── Helpers ─────────────────────────────────────────────────
    private function convertDate(string $date): string
    {
        $parts = explode('/', $date);
        return count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : $date;
    }

    private function parseDecimal(string $v): string
    {
        return number_format((float) str_replace(['.', ','], ['', '.'], $v), 2, '.', '');
    }

    private function normalizeCpf(string $cpf): string
    {
        $d = preg_replace('/\D/', '', $cpf);
        return strlen($d) === 11
            ? substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)
            : $cpf;
    }

    /**
     * Insere espaço nas transições lowercase→Uppercase causadas pela extração do PDF
     * "DeborahRodriguesDido" → "Deborah Rodrigues Dido"
     * "SantaHelenadeGoiás"  → "Santa Helena de Goiás"
     */
    private function splitWords(string $text): string
    {
        // Insere espaço antes de letra maiúscula que segue letra minúscula (ou acentuada)
        $result = preg_replace(
            '/([a-záéíóúãõâêîôûàüçñ])([A-ZÁÉÍÓÚÃÕÂÊÎÔÛÀÜÇÑ])/u',
            '$1 $2',
            $text
        );
        // Remove espaços duplos e trim
        return trim(preg_replace('/\s{2,}/', ' ', $result));
    }
}
