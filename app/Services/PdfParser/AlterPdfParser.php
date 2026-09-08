<?php

namespace App\Services\PdfParser;

use Smalot\PdfParser\Parser;

class AlterPdfParser
{
    private string $text;

    /**
     * Linhas reconstruídas a partir das coordenadas (getDataTm), usadas para
     * recuperar os espaços que o getText() perde ("MATHEUSAFONSO..." → itens
     * separados "MATHEUS", "AFONSO"...). Cada linha: ['tokens' => [...], 'concat' => 'semespacos']
     */
    private array $tmLines = [];

    public function parse(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);

        // Usa apenas as primeiras 3 páginas (dados do contrato)
        $pages = $pdf->getPages();
        $parts = [];
        $this->tmLines = [];
        for ($i = 0; $i < min(3, count($pages)); $i++) {
            $parts[] = $pages[$i]->getText();
            $this->buildTmLines($pages[$i]);
        }
        $this->text = implode("\n", $parts);

        return [
            'codigo_externo'  => $this->extractCodigoExterno(),
            'data_vigencia'   => $this->extractDataVigencia(),
            'data_boleto'     => $this->extractDataAdmissao(),
            'vendedor_cpf'    => $this->extractVendedorCpf(),
            'vendedor_nome'   => $this->desgrudar($this->extractVendedorNome()),
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
    // Layout antigo: "8406058Nº"  /  Layout novo: "N° 8413049"
    private function extractCodigoExterno(): string
    {
        if (preg_match('/(\d{6,10})\s*N[ºo°]/u', $this->text, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/N[ºo°]\s*(\d{6,10})/u', $this->text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ─── Data de admissão (usada como data_boleto) ────────────────
    // Antigo: "DatadeAdmissão\n11/05/2026"
    // Novo:   "CartãodoSUS  DatadeAdmissão/Associação Sexo  EstadoCivil\n11/08/2026  M  SOLTEIRO"
    private function extractDataAdmissao(): string
    {
        if (preg_match('/Data\s*de\s*Admiss[aã]o[^\n]*\n[^\n]*?(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
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
    // Antigo: "CPFdoVendedor\n896.190.211-34"
    // Novo:   "NomedoVendedor(a)  CPF\nFredericoBritodeBarros  586.108.981-72"
    private function extractVendedorCpf(): string
    {
        if (preg_match('/CPF\s*do\s*Vendedor\s*\n\s*([\d]{3}[\.\s][\d]{3}[\.\s][\d]{3}[\-\s][\d]{2})/ui', $this->text, $m)) {
            return $this->normalizeCpf($m[1]);
        }
        if (preg_match('/Nome\s*do\s*Vendedor[^\n]*\n[^\n]*?([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})/ui', $this->text, $m)) {
            return $m[1];
        }
        return '';
    }

    // ─── Nome do vendedor ─────────────────────────────────────────
    // Antigo: "NomedoVendedor\nMarisaAraújo"  /  Novo: "NomedoVendedor(a)  CPF\nFredericoBritodeBarros  586..."
    private function extractVendedorNome(): string
    {
        if (preg_match('/Nome\s*do\s*Vendedor[^\n]*\n\s*([^\t\n]+)/ui', $this->text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ─── Entidade ─────────────────────────────────────────────────
    // Antigo: "Entidade:\nFETRABRAS-FETRACESP"  /  Novo: "Entidade  MêsdeReajuste\nFETRABRAS-FETRACESP  DEZEMBRO"
    private function extractEntidade(): string
    {
        if (preg_match('/Entidade[^\n]*\n\s*([^\t\n]+)/ui', $this->text, $m)) {
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

        // Nome: "Nome( Completo)\nDeborahRodriguesDido" ou "Nome(Completo)\nMATHEUSAFONSO..."
        if (preg_match('/Nome\s*\(\s*Completo\s*\)\s*\n\s*([^\n\t]+)/ui', $this->text, $m)) {
            $t['nome'] = $this->desgrudar(trim($m[1]));
        }

        // Seção do titular (layout antigo tinha marcadores 1–TITULAR / 2–DEPENDENTES)
        $secaoTitular = $this->text;
        if (preg_match('/1\s*[–-]\s*PROPONENTE\s*TITULAR([\s\S]*?)2\s*[–-]\s*DEPENDENTES/ui', $this->text, $ms)) {
            $secaoTitular = $ms[1];
        }

        // CPF — layout novo: linha de labels "Telefone1 Telefone2 RG ÓrgãoExpedidor CPF"
        // com valores na linha seguinte; o CPF é o último da linha de valores
        if (preg_match('/Telefone\s*1[^\n]*CPF[^\n]*\n([^\n]+)/ui', $secaoTitular, $m)
            && preg_match_all('/[\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2}/u', $m[1], $cpfs)) {
            $t['cpf'] = end($cpfs[0]);
        } elseif (preg_match('/\bCPF\b\s*\n\s*([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})/ui', $secaoTitular, $m)) {
            $t['cpf'] = $m[1];
        } else {
            // Último recurso: primeiro CPF do texto que não seja o do vendedor
            $vendedorCpf = $this->extractVendedorCpf();
            if (preg_match_all('/[\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2}/u', $secaoTitular, $todos)) {
                foreach ($todos[0] as $cpf) {
                    if ($cpf !== $vendedorCpf) { $t['cpf'] = $cpf; break; }
                }
            }
        }

        // Data de Nascimento: valor pode estar na linha seguinte ao label, após outros campos
        // "NomeSocial  Idade DatadeNascimento\nMATHEUS...  24  18/06/2002"
        if (preg_match('/Data\s*de\s*Nascimento[^\n]*\n[^\n]*?(\d{2}\/\d{2}\/\d{4})/ui', $secaoTitular, $m)) {
            $t['data_nascimento'] = $this->convertDate($m[1]);
        }

        // Telefone: "Telefone1 ...\n(62)99273-4145 ..." — exige formato de telefone
        // para não capturar texto de outra seção
        if (preg_match('/Telefone\s*1?[^\n]*\n\s*(\(?\d{2}\)?\s*\d{4,5}[\-\s]?\d{4})/ui', $secaoTitular, $m)) {
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

        // CEP/Bairro/Cidade/UF — layout novo: labels juntos e valores na linha seguinte
        // "CEP  Bairro  Cidade  UF\n74815-435  VilaSaoJoao  Goiania  GO"
        if (preg_match('/\bCEP\b[^\n]*Bairro[^\n]*Cidade[^\n]*UF[^\n]*\n\s*([^\n]+)/ui', $secaoTitular, $m)) {
            $partes = preg_split('/\t+/', trim($m[1]));
            if (count($partes) >= 4) {
                $t['cep']    = trim($partes[0]);
                $t['bairro'] = $this->desgrudar(trim($partes[1]));
                $t['cidade'] = $this->desgrudar(trim($partes[2]));
                $t['uf']     = strtoupper(trim($partes[3]));
            }
        }
        if ($t['cep'] === '' && preg_match('/\bCEP\b\s*\n\s*([\d]{5}-?[\d]{3})/ui', $secaoTitular, $m)) {
            $t['cep'] = $m[1];
        }

        // Endereço / Rua: "EndereçoResidencial  Nº  Complemento\nRua109  290  Apto301"
        if (preg_match('/Endere[cç]o\s*(?:Residencial)?[^\n]*\n\s*([^\n]+)/ui', $secaoTitular, $m)) {
            $partes = preg_split('/\t+/', trim($m[1]));
            $rua = $this->desgrudar(trim($partes[0]));
            if (!empty($partes[1])) $rua .= ', ' . trim($partes[1]);
            if (!empty($partes[2])) $rua .= ' ' . $this->desgrudar(trim($partes[2]));
            $t['rua'] = $rua;
        }

        // Bairro/Cidade — layout antigo (labels isolados)
        if ($t['bairro'] === '' && preg_match('/\bBairro\b\s*\n\s*([^\n\t]+)/ui', $secaoTitular, $m)) {
            $t['bairro'] = $this->desgrudar(trim($m[1]));
        }
        if ($t['cidade'] === '' && preg_match('/\bCidade\b\s*\n\s*([^\n\t]+)/ui', $secaoTitular, $m)) {
            $t['cidade'] = $this->desgrudar(trim($m[1]));
        }
        if ($t['uf'] === '' && preg_match('/\bUF\b\s*\n\s*([A-Z]{2})\b/u', $secaoTitular, $m)) {
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

        // Layout novo: "NOMEDOPLANOPRETENDIDO:  NOSSOPLANOENFCOPARTTOTAL-GOIANIA..."
        if (preg_match('/NOME\s*DO\s*PLANO\s*PRETENDIDO\s*:?\s*([^\n]+)/ui', $this->text, $m)) {
            $plano['nome'] = trim($m[1]);
        } elseif (preg_match('/\bPlano\b\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            $plano['nome'] = trim($m[1]);
        }

        // Código ANS: "CódigoANS\n482993190" ou "CÓDIGOANSDOPLANOPRETENDIDO:  487817205"
        if (preg_match('/C[oó]digo\s*ANS[^\n\d]*(\d{6,12})/ui', $this->text, $m)) {
            $plano['codigo_ans'] = $m[1];
        } elseif (preg_match('/C[oó]digo\s*ANS[^\n]*\n\s*(\d{6,12})/ui', $this->text, $m)) {
            $plano['codigo_ans'] = $m[1];
        }

        // Acomodação: "Acomodação\nENFERMARIA" ou "ACOMODAÇÃODOPLANOPRETENDIDO:  ENFERMARIA"
        if (preg_match('/Acomoda[cç][aã]o[^\n]*?(ENFERMARIA|APARTAMENTO)/ui', $this->text, $m)) {
            $plano['acomodacao'] = strtoupper($m[1]);
        } elseif (preg_match('/Acomoda[cç][aã]o[^\n]*\n\s*(ENFERMARIA|APARTAMENTO)/ui', $this->text, $m)) {
            $plano['acomodacao'] = strtoupper($m[1]);
        }

        // Coparticipação: "FatorModerador–Coparticipação\nSIM" ou "FATORDEMODERAÇÃO...: SIM"
        if (preg_match('/FATOR\s*(?:DE)?\s*MODERA[CÇ][AÃ]O[^\n]*?(SIM|N[ÃA]O)/ui', $this->text, $m)) {
            $plano['coparticipacao'] = strtoupper(trim($m[1])) === 'SIM';
        } elseif (preg_match('/FatorModerador[^\n]*\n\s*(SIM|N[ÃA]O)/ui', $this->text, $m)) {
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
    // Texto real: "VALORTOTALEM R$TITULAR+DEPENDENTES\n292,34" ou "ValorTotalEmR$Titular+Dependentes  238,55"
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
        if (preg_match('/VALOR\s*TOTAL[^\d]*([\d\.]+,\d{2})/ui', $this->text, $m)) {
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

    /**
     * Reconstrói as linhas da página a partir das coordenadas dos blocos de
     * texto (getDataTm). O getText() do smalot cola as palavras
     * ("MATHEUSAFONSO..."), mas os blocos vêm separados com posição X/Y.
     */
    private function buildTmLines($page): void
    {
        try {
            $data = $page->getDataTm();
        } catch (\Throwable $e) {
            return; // sem dados posicionais nesta página — desgrudar cai no splitWords
        }

        $linhas = [];
        foreach ($data as $item) {
            if (!isset($item[0][4], $item[0][5], $item[1])) continue;
            $x = (float) $item[0][4];
            $y = (float) $item[0][5];
            $txt = trim((string) $item[1]);
            if ($txt === '') continue;

            $chave = null;
            foreach (array_keys($linhas) as $yk) {
                if (abs((float) $yk - $y) <= 2.0) { $chave = $yk; break; }
            }
            if ($chave === null) { $chave = (string) $y; $linhas[$chave] = []; }
            $linhas[$chave][] = [$x, $txt];
        }

        foreach ($linhas as $itens) {
            usort($itens, fn($a, $b) => $a[0] <=> $b[0]);
            // Junta sequências de tokens de 1 caractere (campos renderizados letra a letra)
            $tokens = [];
            $prevLen1 = false;
            foreach ($itens as [$x, $txt]) {
                $len1 = mb_strlen($txt) === 1;
                if ($len1 && $prevLen1 && $tokens) {
                    $tokens[count($tokens) - 1] .= $txt;
                } else {
                    $tokens[] = $txt;
                }
                $prevLen1 = $len1;
            }
            $this->tmLines[] = [
                'tokens' => $tokens,
                'concat' => preg_replace('/\s+/u', '', implode('', $tokens)),
            ];
        }
    }

    /**
     * Recupera os espaços de um valor que o getText() colou, procurando a
     * linha posicional cujos tokens formam o mesmo texto.
     * "MATHEUSAFONSOROSABARBOSASILVA" → "MATHEUS AFONSO ROSA BARBOSA SILVA"
     * Se não encontrar, cai no splitWords (heurística minúscula→Maiúscula).
     */
    private function desgrudar(string $valor): string
    {
        $valor = trim($valor);
        $alvo = preg_replace('/\s+/u', '', $valor);
        if ($alvo === '') return $valor;

        foreach ($this->tmLines as $linha) {
            $pos = mb_strpos($linha['concat'], $alvo);
            if ($pos === false) continue;

            $fim = $pos + mb_strlen($alvo);
            $cursor = 0;
            $partes = [];
            foreach ($linha['tokens'] as $tok) {
                $tokLimpo = preg_replace('/\s+/u', '', $tok);
                $len = mb_strlen($tokLimpo);
                if ($cursor + $len > $pos && $cursor < $fim) {
                    $partes[] = $tok;
                }
                $cursor += $len;
            }
            $cand = trim(preg_replace('/\s{2,}/', ' ', implode(' ', $partes)));
            if (count($partes) > 1 && preg_replace('/\s+/u', '', $cand) === $alvo) {
                return $cand;
            }
        }

        return $this->splitWords($valor);
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

    private function normalizeCpf(string $cpf): string
    {
        $d = preg_replace('/\D/', '', $cpf);
        return strlen($d) === 11
            ? substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)
            : $cpf;
    }

    /**
     * Insere espaço nas transições lowercase→Uppercase e letra↔dígito
     * causadas pela extração do PDF
     * "DeborahRodriguesDido" → "Deborah Rodrigues Dido"
     * "Rua109"              → "Rua 109"
     */
    private function splitWords(string $text): string
    {
        $result = preg_replace(
            '/([a-záéíóúãõâêîôûàüçñ])([A-ZÁÉÍÓÚÃÕÂÊÎÔÛÀÜÇÑ])/u',
            '$1 $2',
            $text
        );
        // Letra seguida de dígito (e vice-versa)
        $result = preg_replace('/(\p{L})(\d)/u', '$1 $2', $result);
        $result = preg_replace('/(\d)(\p{L})/u', '$1 $2', $result);
        // Remove espaços duplos e trim
        return trim(preg_replace('/\s{2,}/', ' ', $result));
    }
}
