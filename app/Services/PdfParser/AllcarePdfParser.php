<?php

namespace App\Services\PdfParser;

use Smalot\PdfParser\Parser;

class AllcarePdfParser
{
    private string $text;

    public function parse(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);

        // Usa apenas as primeiras 10 páginas (restante são scans físicos)
        $pages = $pdf->getPages();
        $parts = [];
        for ($i = 0; $i < min(10, count($pages)); $i++) {
            $parts[] = $pages[$i]->getText();
        }
        $this->text = implode("\n", $parts);

        return [
            'codigo_externo'  => $this->extractCodigoExterno(),
            'data_vigencia'   => $this->extractDataVigencia(),
            'data_boleto'     => $this->extractDataVigencia(),
            'vendedor_cpf'    => $this->extractVendedorCpf(),
            'vendedor_nome'   => $this->extractVendedorNome(),
            'administradora'  => 'Allcare',
            'entidade'        => $this->extractEntidade(),
            'titular'         => $this->extractTitular(),
            'plano'           => $this->extractPlano(),
            'valor_plano'     => $this->extractValorTotal(),
            'valor_adesao'    => $this->extractValorTotal(),
            'dependentes'     => $this->extractDependentes(),
        ];
    }

    // ─── Nº do contrato ─────────────────────────────────────────
    // Texto: "Nº Contrato: 74879 / 77638" → pega o primeiro número
    private function extractCodigoExterno(): string
    {
        if (preg_match('/N[ºo°]\s*Contrato\s*[:.]?\s*([\d]+)/ui', $this->text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ─── Data de vigência ────────────────────────────────────────
    // Texto: "Inicio de Vigência\n20/01/2026"
    private function extractDataVigencia(): string
    {
        if (preg_match('/In[íi]cio\s+de\s+Vig[êe]ncia\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            return $this->convertDate($m[1]);
        }
        if (preg_match('/Ades[ãa]o\s+e\s+Vig[êe]ncia\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            return $this->convertDate($m[1]);
        }
        // Fallback: data do início que aparece junto ao vencimento do boleto
        if (preg_match('/(?:Todo\s+dia[^\n]*?)(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            return $this->convertDate($m[1]);
        }
        return '';
    }

    // ─── CPF do vendedor ─────────────────────────────────────────
    // Texto: "896.190.211-34\nCPF do Vendedor"  ← CPF vem ANTES do label
    private function extractVendedorCpf(): string
    {
        if (preg_match('/([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})\s*\nCPF do Vendedor/ui', $this->text, $m)) {
            return $m[1];
        }
        // Fallback: "CPF do Vendedor\nXXX.XXX.XXX-XX"
        if (preg_match('/CPF do Vendedor\s*\n\s*([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})/ui', $this->text, $m)) {
            return $m[1];
        }
        return '';
    }

    // ─── Nome do vendedor ─────────────────────────────────────────
    // Texto: "Nome do Vendedor\nMarisa Dias De Araujo"
    private function extractVendedorNome(): string
    {
        if (preg_match('/Nome do Vendedor\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ─── Entidade ─────────────────────────────────────────────────
    // Texto: "AEB - Associacao Dos Estudantes Do Brasil\n01 / 26\nNome da Entidade:"
    // O valor vem ANTES do label "Nome da Entidade:"
    private function extractEntidade(): string
    {
        if (preg_match('/([^\n]+)\s*\n\s*\d+\s*\/\s*\d+\s*\nNome da Entidade:/ui', $this->text, $m)) {
            return trim($m[1]);
        }
        // Fallback direto
        if (preg_match('/Nome da Entidade:\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
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

        // Nome + CPF do footer (repete em todas as páginas)
        // "Chris Kellen Rodrigues Barbosa\nCpf: 739.829.971-00"
        if (preg_match('/([A-ZÁÉÍÓÚÃÕÂÊÎÔÛÀÜ][A-ZÁÉÍÓÚÃÕÂÊÎÔÛÀÜa-záéíóúãõâêîôûàüçñ\s]+)\s*\nCpf:\s*([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})/u', $this->text, $m)) {
            $t['nome'] = trim($m[1]);
            $t['cpf']  = $m[2];
        }

        // Data de Nascimento - primeira ocorrência na seção do titular
        // Titular com 13 anos (em Jan/2026) → nasceu em 2012
        // Texto: "Data de Nascimento\n17/08/2012"
        if (preg_match('/Data de Nascimento\s*\n\s*(\d{2}\/\d{2}\/\d{4})/ui', $this->text, $m)) {
            $t['data_nascimento'] = $this->convertDate($m[1]);
        }

        // Email: "E-mail\nchriskellen2021@gmail.com"
        if (preg_match('/E-mail\s*\n\s*([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/ui', $this->text, $m)) {
            $t['email'] = strtolower(trim($m[1]));
        }

        // Celular: "Telefone Celular\n9.8342-7387\nDDD\n62"
        // O número vem ANTES do DDD na extração
        if (preg_match('/Telefone Celular\s*\n\s*([\d\.\-]+)\s*\nDDD\s*\n\s*(\d{2})/ui', $this->text, $m)) {
            $num = preg_replace('/\D/', '', $m[1]);
            $t['celular'] = '(' . $m[2] . ') ' . $num;
        } elseif (preg_match('/\(?([\d]{2})\)?\s*(9[\d]{4}[\.\-\s]?[\d]{4})/u', $this->text, $m)) {
            $t['celular'] = '(' . $m[1] . ') ' . preg_replace('/\D/', '', $m[2]);
        }

        // CEP: "CEP\n74974610" → formata como "74974-610"
        if (preg_match('/\bCEP\b\s*\n\s*(\d{5})\-?(\d{3})/ui', $this->text, $m)) {
            $t['cep'] = $m[1] . '-' . $m[2];
        }

        // Logradouro/Rua: "Logradouro\nRua Calcedonia"
        if (preg_match('/Logradouro\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            $t['rua'] = trim($m[1]);
        }

        // Bairro: "Bairro\nVirginia Parque"
        if (preg_match('/\bBairro\b\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            $t['bairro'] = trim($m[1]);
        }

        // Município/Cidade: "Município\nAparecida De Goiania"
        if (preg_match('/Munic[íi]pio\s*\n\s*([^\n]+)/ui', $this->text, $m)) {
            $t['cidade'] = trim($m[1]);
        }

        // UF: "UF\nGO"
        if (preg_match('/\bUF\b\s*\n\s*([A-Z]{2})\b/u', $this->text, $m)) {
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

        // Nome + ANS: "Nosso Plano Aho Ca Gm Enf Cc S / 487817205"
        // Página 2: linha do plano contratado
        if (preg_match('/(Nosso\s+Plano[^\n\/]+)\/\s*([\d]{6,12})/ui', $this->text, $m)) {
            $plano['nome']       = trim($m[1]);
            $plano['codigo_ans'] = trim($m[2]);
        } elseif (preg_match('/Nosso\s+Plano\s+([^\n]+)/ui', $this->text, $m)) {
            $plano['nome'] = trim($m[1]);
        }

        // Acomodação
        if (preg_match('/(Enfermaria|Apartamento)/ui', $this->text, $m)) {
            $plano['acomodacao'] = strtoupper($m[1]);
        }

        // Coparticipação: "Com coparticipacao"
        if (preg_match('/Com\s+coparticipa[cç][ãa]o/ui', $this->text)) {
            $plano['coparticipacao'] = true;
        }

        // Odonto: linha de produto odontológico com "Odontologico" ou "9984 +Odonto"
        if (preg_match('/Odontol[óo]gico|9984\s*\+Odonto/ui', $this->text)) {
            $plano['odonto'] = true;
        }

        return $plano;
    }

    // ─── Valor total ─────────────────────────────────────────────
    // Texto (pág. 7): "Titular   13   R$ 224.77   R$ 0.00   R$ 224.77"
    // Formato Allcare usa PONTO como decimal (ex: R$ 224.77)
    private function extractValorTotal(): string
    {
        // Localiza a seção de valores mensais
        $secao = $this->text;
        if (preg_match('/Valores mensais previstos([\s\S]{0,2000})/ui', $this->text, $m)) {
            $secao = $m[1];
        }

        // Extrai todas as linhas da tabela: cada linha tem 3 colunas "R$ X.XX"
        // A última coluna (Total) é a que nos interessa — soma de todas as linhas
        $total = 0.0;
        // Captura grupos de 3 valores R$ numa mesma linha (Saude | Odonto | Total)
        if (preg_match_all('/R\$\s*([\d\.]+)\s+R\$\s*([\d\.]+)\s+R\$\s*([\d\.]+)/u', $secao, $matches)) {
            foreach ($matches[3] as $v) {
                $total += (float) $v;
            }
            if ($total > 0) {
                return number_format($total, 2, '.', '');
            }
        }

        // Fallback: soma todos os "R$ X.XX" e pega o maior
        if (preg_match_all('/R\$\s*([\d\.]+)/u', $secao, $matches)) {
            $values = array_map('floatval', $matches[1]);
            $max = max($values);
            if ($max > 0) {
                return number_format($max, 2, '.', '');
            }
        }

        return '0.00';
    }

    // ─── Dependentes ─────────────────────────────────────────────
    private function extractDependentes(): array
    {
        $dependentes = [];

        // Localiza a seção "Relação dos Dependentes"
        $secao = '';
        if (preg_match('/Rela[cç][aã]o dos Dependentes([\s\S]*?)(?:Assinatura Digital|\z)/ui', $this->text, $m)) {
            $secao = $m[1];
        }
        if (empty(trim($secao))) return $dependentes;

        // Cada dependente preenchido: CPF vem ANTES do label, nome vem DEPOIS
        // "103.747.971-88\nCPF\tNome Completo...\nJuan Pablo De Abreu Barbosa"
        $blocos = preg_split('/\n\s*\d+\s*\n\s*ARC\s*/ui', $secao);
        foreach ($blocos as $bloco) {
            $bloco = trim($bloco);
            if (empty($bloco)) continue;

            $cpf  = '';
            $nome = '';

            // CPF: antes da label "CPF   Nome Completo..."
            if (preg_match('/([\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2})\s*\n\s*CPF/ui', $bloco, $mc)) {
                $cpf = $mc[1];
            }
            // Nome: depois da label "CPF	Nome Completo..."
            if (preg_match('/CPF[^\n]*Nome[^\n]*\n\s*([^\n]+)/ui', $bloco, $mn)) {
                $nome = trim($mn[1]);
                // Ignora se for um nome de label ou repetição
                if (preg_match('/^(Nº|Nome Completo|Chris Kellen|Assinatura)/ui', $nome)) {
                    $nome = '';
                }
            }

            // Só inclui se tiver CPF e nome
            if (!empty($cpf) && !empty($nome)) {
                $dependentes[] = ['nome' => $nome, 'cpf' => $cpf];
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
}
