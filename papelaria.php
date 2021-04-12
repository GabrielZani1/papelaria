<?php

const CLIENTE = 'Cliente';
const FORNECEDOR = 'Fornecedor';
const RELAT_EST_TOTAL = 1;
const RELAT_EST_ITEM = 2;
const RELAT_MOV_ITEM = 3;
const RELAT_MOV_DATA = 4;
date_default_timezone_set('America/Porto_Velho');

iniciarAplicacao();

function iniciarAplicacao() {
    validaTerminais(); 

    while (true) {        
        system('clear');
        echo "|==================================================================|\n";
        echo "|                          MENU PAPELARIA 1235                     |\n";
        echo "|==================================================================|\n";
        echo "| ".mb_str_pad("(Digite 1) - Cadastro de Cliente.", 65, " ")."|\n";
        echo "| ".mb_str_pad("(Digite 2) - Cadastro de Fornecedor.", 65, " ")."|\n";
        echo "| ".mb_str_pad("(Digite 3) - Cadastro de Categoria.", 65, " ")."|\n";
        echo "| ".mb_str_pad("(Digite 4) - Cadastro de Produto(s).", 65, " ")."|\n";
        echo "| ".mb_str_pad("(Digite 5) - Entrada de Produto.", 65, " ")."|\n";
        echo "| ".mb_str_pad("(Digite 6) - Saída de Produto.", 65, " ")."|\n";
        echo "| ".mb_str_pad("(Digite 7) - Relatório Atual de Estoque.", 65, " ")."|\n";
        echo "| ".mb_str_pad("(Digite 8) - Relatório Atual de Estoque por Item Específico.", 65, " ")."|\n";
        echo "| ".mb_str_pad("* Para sair precione a tecla Enter.", 65, " ")."|\n";
        echo "|==================================================================|\n";
        $opcao_menu = (int) readline("->");

        system('clear');
        switch ($opcao_menu) {
            case 1:
                cadastroClienteFornecedor('Cliente');
                break;
            case 2:
                cadastroClienteFornecedor('Fornecedor');
                break;
            case 3:
                cadastroCategoria();
                break;
            case 4:
                cadastroProduto();
                break;
            case 5:
                entradaItem();
                break;
            case 6:
                saidaItem();
                break;
            case 7:
                relatorioAtualEstoque();
                break;
            case 8:
                relatorioMovimentacaoEstoque();
                break;
            default:
                return false;                                       
        }
    }
}

function cadastroClienteFornecedor($tipo_cad) {
    include 'conexao/conexao.php'; 

    while (true) {  
        $limite = 0; 
        $valida = false;        

        system('clear');
        echo "|==========================================================================|\n";
        echo "|                           Cadastro do ".mb_str_pad($tipo_cad, 34, " ")." |\n";        
        echo "|==========================================================================|\n\n";                 

        echo "* Dados do ".$tipo_cad." *\n";
        $nome = readline("Informe o nome: ");                    
        if ($tipo_cad == CLIENTE) {             
            $cpf_cli = readline("Informe o CPF (apenas número): ");
        } else {             
            $cnpj_forn = readline("Informe o CNPJ (apenas número): ");
            $ie_forn = readline("Informe a Inscrição Estadual: ");
        }
        $contato = readline("Informe seu Contato: ");   

        echo "\n* Endereço do ".$tipo_cad." *\n";
        $rua = readline("Informe o nome da Rua: "); 
        $numero = readline("Informe o número: "); 
        $complemento = readline("Complemento: "); 
        $bairro = readline("Bairro: ");
        $cep = readline("CEP: ");

        $sqlEstado = "SELECT id_estado, nome_estado FROM estado"; 
        $retornoEst = $conexao->prepare($sqlEstado);    
        $retornoEst->execute();
        $regEst = $retornoEst->fetchAll(PDO::FETCH_OBJ); 

        echo "\n|=========================================================================|\n";    
        echo "|                                  Estados  123                           |\n";    
        echo "|=========================================================================|\n";    
        foreach ($regEst as $estado) {        
            echo "|".mb_str_pad($estado->id_estado."-".$estado->nome_estado, 23, " ")."|";         
            $limite ++; 

            if ($limite == 3) {
                echo "\n";
                $limite = 0;
            }      
        }
        echo "|=========================================================================|\n"; 
        $id_estado = (int) readline("Informe o código do Estado: ");

        $sqlMuni = "SELECT id_cidade, nome_cidade FROM cidade WHERE id_estado = :id_estado";
        $retornoMuni = $conexao->prepare($sqlMuni);
        $retornoMuni->bindParam(':id_estado', $id_estado);
        $retornoMuni->execute();
        $regMuni = $retornoMuni->fetchAll(PDO::FETCH_OBJ);

        if ($regMuni) {
            $limite = 0;
            echo "\n|==========================================================================|\n";    
            echo "|                               Municípios                                 |\n";    
            echo "|==========================================================================|\n";    
            foreach ($regMuni as $rgMuni) {
                echo "|".mb_str_pad($rgMuni->id_cidade."-".$rgMuni->nome_cidade, 36, " ")."|";
                $limite ++;   

                if ($limite == 2) {
                    echo "\n";
                    $limite = 0;
                }
            }
            echo "\n|==========================================================================|\n";    
            $id_municipio = (int) readline("Informe o código do Município: ");

            if ($tipo_cad == CLIENTE) {
                if (!empty($cpf_cli)) {
                    $valida = true;
                }
            } else {
                if (!empty($cnpj_forn) && !empty($ie_forn)) {
                    $valida = true;
                }
            }            

            if ($valida && !empty($nome) && !empty($contato) && !empty($rua) && !empty($numero) 
                && !empty($bairro) && !empty($cep) && $id_municipio != 0) {
                
                $sqlInsertBairro = "INSERT INTO bairro VALUES (NULL, :nome_bairro, :cep_bairro, :id_cidade)";
                $dadosInsertBairro = $conexao->prepare($sqlInsertBairro);
                $dadosInsertBairro->bindParam(':nome_bairro', $bairro);
                $dadosInsertBairro->bindParam(':cep_bairro', $cep);
                $dadosInsertBairro->bindParam(':id_cidade', $id_municipio);
                $retornoInsertBairro = $dadosInsertBairro->execute();
                
                if ($retornoInsertBairro) {
                    $id_bairro = $conexao->lastInsertId();

                    $sqlInsertEnd = "INSERT INTO endereco VALUES (NULL, :rua, :numero, :complemento, :id_bairro)";
                    $dadosInsertEnd = $conexao->prepare($sqlInsertEnd);
                    $dadosInsertEnd->bindParam(':rua', $rua);
                    $dadosInsertEnd->bindParam(':numero', $numero);
                    $dadosInsertEnd->bindParam(':complemento', $complemento);
                    $dadosInsertEnd->bindParam(':id_bairro', $id_bairro);
                    $retornoInsertEnd = $dadosInsertEnd->execute(); 

                    if ($retornoInsertEnd) {
                        $id_end = $conexao->lastInsertId();

                        if ($tipo_cad == CLIENTE) {
                            $sqlInsertCli = "INSERT INTO cliente VALUES (NULL, :nome_cliente, :cpf, :contato_cli, :id_endereco)";
                            $dadosInsertCli = $conexao->prepare($sqlInsertCli);
                            $dadosInsertCli->bindParam(':nome_cliente', $nome);
                            $dadosInsertCli->bindParam(':cpf', $cpf_cli);
                            $dadosInsertCli->bindParam(':contato_cli', $contato);
                            $dadosInsertCli->bindParam(':id_endereco', $id_end);
                            $retornoInsertCli = $dadosInsertCli->execute();
    
                            return retornoCadastro($retornoInsertCli, true);
                        } else {
                            $sqlInsertForne = "INSERT INTO fornecedor VALUES (NULL, :nome_fornecedor, :cnpj_forne, :contato_forne, :ie_forne, :id_endereco)";
                            $dadosInsertForne = $conexao->prepare($sqlInsertForne);
                            $dadosInsertForne->bindParam(':nome_fornecedor', $nome);
                            $dadosInsertForne->bindParam(':cnpj_forne', $cnpj_forn);
                            $dadosInsertForne->bindParam(':contato_forne', $contato);
                            $dadosInsertForne->bindParam(':ie_forne', $ie_forn);
                            $dadosInsertForne->bindParam(':id_endereco', $id_end);
                            $retornoInsertForne = $dadosInsertForne->execute();
    
                            return retornoCadastro($retornoInsertForne, true);
                        }
                    }
                }
            } else {
                echo "\nATENÇÃO!!!\nÉ necessário preencher todas as informações.\n";
                echo "  (Digite 1) - Para iniciar o cadastro novamente.\n";
                echo "  Para voltar ao Menu precione a tecla Enter.\n";
                $opcao_cad = (int) readline("->");   
                
                if ($opcao_cad != 1) {
                    return false; 
                }            
            }
        } else {
            return false;
        }
    } 
}

function cadastroCategoria() {
    include 'conexao/conexao.php';
    $nome_categoria = "";
                 
    system('clear');
    echo "|==================================================================|\n";
    echo "|                      Cadastro de Categoria                       |\n";
    echo "|==================================================================|\n";                 
    $nome_categoria = readline("Informe o tipo de categoria: "); 
    
    if (!empty($nome_categoria)) {            
        $sqlTem = "SELECT id_categoria FROM categoria WHERE nome_categoria = :nome_categoria";    
        $retornoCat = $conexao->prepare($sqlTem);
        $retornoCat->bindParam(':nome_categoria', $nome_categoria);
        $retornoCat->execute();
        $regCat = $retornoCat->fetchAll(PDO::FETCH_OBJ);               

        if (count($regCat) > 0) {                
            echo "\n\nATENÇÃO!!!\nO tipo de categoria informada já existe.\nPor favor informe outro tipo de categoria.\n";
            readline("");
            $nome_categoria = "";
        } else {
            $sqlInsertCat = "INSERT INTO categoria VALUES (NULL, :nome_categoria)";
            $dadosInsertCat = $conexao->prepare($sqlInsertCat);
            $dadosInsertCat->bindParam(':nome_categoria', $nome_categoria);
            $retornoInsertCat = $dadosInsertCat->execute();
            
            return retornoCadastro($retornoInsertCat, true);
        }
    }   
}

function cadastroProduto() {
    include 'conexao/conexao.php'; 
    
    while (true) {
        $limite = 0;

        system('clear');
        echo "|==================================================================|\n";
        echo "|                       Cadastro de Produto                        |\n";
        echo "|==================================================================|\n\n"; 
        
        echo "* Informações do Produto *\n";
        $desc_prod = readline("Informe a descrição do produto: "); 
        $peso_prod = readline("Informe o peso do produto: "); 
        $valor_prod = (double) readline("Informe o valor do produto: "); 
        $qtd_prod = (int) readline("Informe a quantidade do produto: "); 

        $sqlCat = "SELECT id_categoria, nome_categoria FROM categoria";    
        $retornoCat = $conexao->prepare($sqlCat);    
        $retornoCat->execute();
        $regCat = $retornoCat->fetchAll(PDO::FETCH_OBJ);

        if ($regCat) {
            echo "\n|==================================================================|\n";
            echo "|                     Categoria do(s) Produto(s)                   |\n";
            echo "|==================================================================|\n"; 
            foreach ($regCat as $reg) {            
                echo "|".mb_str_pad($reg->id_categoria."-".$reg->nome_categoria, 32, " ")."|";
                $limite ++;   

                if ($limite == 2) {
                    echo "\n";
                    $limite = 0;
                }
            }
            echo "\n|==================================================================|\n"; 
            $id_cat_prod = (int) readline("Informe o código da Categoria do Produto: ");

            $sqlForne = "SELECT id_fornecedor, nome_fornecedor FROM fornecedor";
            $retornoForne = $conexao->prepare($sqlForne);
            $retornoForne->execute();
            $regForne = $retornoForne->fetchAll(PDO::FETCH_OBJ);

            if ($regForne) {  
                $limite = 0;          
                echo "\n|==================================================================|\n";
                echo "|                             Fornecedor                           |\n";
                echo "|==================================================================|\n";
                foreach ($regForne as $regF) {
                    echo "|".mb_str_pad($regF->id_fornecedor."-".$regF->nome_fornecedor, 32, " ")."|";
                    $limite ++;   
        
                    if ($limite == 2) {
                        echo "\n";
                        $limite = 0;
                    }
                }
                echo "\n|==================================================================|\n";
                $id_fornecedor = (int) readline("Informe o código do Fornecedor: "); 

                if (!empty($desc_prod) && !empty($peso_prod) && $valor_prod != 0 && $id_cat_prod != 0 && $id_fornecedor != 0) {
                    $sqlInsertCadProd = "INSERT INTO produto VALUES (NULL, :descricao, :peso, :valor, :quantidade_prod, :id_categoria, :id_fornecedor)";
                    $dadosInsertCadProd = $conexao->prepare($sqlInsertCadProd);
                    $dadosInsertCadProd->bindParam(':descricao', $desc_prod);
                    $dadosInsertCadProd->bindParam(':peso', $peso_prod);
                    $dadosInsertCadProd->bindParam(':valor', $valor_prod);
                    $dadosInsertCadProd->bindParam(':quantidade_prod', $qtd_prod);
                    $dadosInsertCadProd->bindParam(':id_categoria', $id_cat_prod);
                    $dadosInsertCadProd->bindParam(':id_fornecedor', $id_fornecedor);
                    $retornoInsertCadProd = $dadosInsertCadProd->execute();
                    
                    return retornoCadastro($retornoInsertCadProd, true);                       
                } else {
                    echo "\nATENÇÃO!!!\nÉ necessário preencher todas as informações.\n";
                    echo "  (Digite 1) - Para iniciar o cadastro novamente.\n";
                    echo "  Para voltar ao Menu precione a tecla Enter.\n";
                    $opcao_cad = (int) readline("->");   
                    
                    if ($opcao_cad != 1) {
                        return false; 
                    } 
                }
            } else {
                echo "\n\nATENÇÃO!!!\nNão existe nenhum registro de Fornecedor cadastrado.\n";
                readline(""); 
                return false; 
            }
        } else {
            echo "\n\nATENÇÃO!!!\nNão existe nenhum registro de Categoria de Produto cadastrado.\n";
            readline("");
            return false;         
        }
    }   
}

function entradaItem() {
    include 'conexao/conexao.php';
    
    while (true) {
        $limite = 0;        

        system('clear');
        echo "|==================================================================|\n";
        echo "|                        Entrada de Produto                        |\n";
        echo "|==================================================================|\n";

        $sqlProdEnt = "SELECT id_produto, descricao FROM produto";
        $retornoProdEnt = $conexao->prepare($sqlProdEnt);
        $retornoProdEnt->execute();
        $regProdEnt = $retornoProdEnt->fetchAll(PDO::FETCH_OBJ);

        foreach ($regProdEnt as $rgEnt) {        
            echo "|".mb_str_pad($rgEnt->id_produto."-".$rgEnt->descricao, 32, " ")."|";
            $limite ++;   

            if ($limite == 2) {
                echo "\n";
                $limite = 0;
            }
        }
        echo "\n|==================================================================|\n";
        $id_prod = (int) readline("Informe o código do Produto: ");

        echo "\n* Informações de Entrada do Produto *\n";
        $lote_produ = (int) readline("Informe o número do Lote: "); 
        $qtd_produ = (int) readline("Informe a quantidade do produto: ");        
        $valor_produ = (double) readline("Informe o valor do produto: ");   

        if ($id_prod != 0 && $lote_produ != 0 && $qtd_produ != 0 && $valor_produ != 0) { 
            $data_cad = date('d/m/Y H:i');

            $sqlEntrada = "INSERT INTO item_entrada VALUES (NULL, :lote, :quantidade, :valor, :data_entrada, :id_produto)";
            $dadosInsertEnt = $conexao->prepare($sqlEntrada);
            $dadosInsertEnt->bindParam(':lote', $lote_produ);
            $dadosInsertEnt->bindParam(':quantidade', $qtd_produ);
            $dadosInsertEnt->bindParam(':valor', $valor_produ);
            $dadosInsertEnt->bindParam(':data_entrada', $data_cad);
            $dadosInsertEnt->bindParam(':id_produto', $id_prod);            
            $retornoInsertEnt = $dadosInsertEnt->execute();
            
            if ($retornoInsertEnt) {
                $sqlUpdProd = "UPDATE produto SET valor_prod = :valor_prod, quantidade_prod = quantidade_prod + :quantidade_prod 
                               WHERE id_produto = :id_produto";
                $updateProd = $conexao->prepare($sqlUpdProd);
                $updateProd->bindParam(':valor_prod',$valor_produ);
                $updateProd->bindParam(':quantidade_prod',$qtd_produ);
                $updateProd->bindParam(':id_produto',$id_prod);
                $retornoUpd = $updateProd->execute(); 

                return retornoCadastro($retornoUpd, true); 
            }
        } else {
            echo "\nATENÇÃO!!!\nÉ necessário preencher todas as informações.\n";
            echo "  (Digite 1) - Para iniciar a entrada do produto novamente.\n";
            echo "  Para voltar ao Menu precione a tecla Enter.\n";
            $opcao_entrada = (int) readline("->");   
            
            if ($opcao_entrada != 1) {
                return false; 
            } 
        }
    }
}

function saidaItem() {
    include 'conexao/conexao.php';
    
    while (true) {
        $limite = 0;     
        $valida_qtd = 0;   
        $array_prod = [];

        system('clear');
        echo "|========================================================|\n";
        echo "|                    Saída de Produto                    |\n";
        echo "|========================================================|\n";

        $sqlProdSaida = "SELECT id_produto, descricao, quantidade_prod, valor_prod FROM produto";
        $retornoProdSaida = $conexao->prepare($sqlProdSaida);
        $retornoProdSaida->execute();
        $regProdSaida = $retornoProdSaida->fetchAll(PDO::FETCH_OBJ);

        foreach ($regProdSaida as $rgSaida) {        
            echo "|".mb_str_pad($rgSaida->id_produto."-".$rgSaida->descricao, 28, " ")."|Qtd: ".mb_str_pad($rgSaida->quantidade_prod, 4, " ")."|Valor: R$ ".mb_str_pad($rgSaida->valor_prod, 7, " ")."|";
            $array_prod[$rgSaida->id_produto] = ['qtd' => $rgSaida->quantidade_prod];
            $limite ++;   

            if ($limite == 1) {
                echo "\n";
                $limite = 0;
            }
        }
        
        echo "|========================================================|\n";
        $id_prod = (int) readline("Informe o código do Produto: ");
        
        if (array_key_exists($id_prod, $array_prod)) {
            $valida_qtd = (int) $array_prod[$id_prod]['qtd'];
        }     

        $limite = 0;        
        echo "\n|========================================================|\n";
        echo "|                        Cliente                         |\n";
        echo "|========================================================|\n";

        $sqlCliente = "SELECT id_cliente, nome_cliente FROM cliente";
        $retornoCliente = $conexao->prepare($sqlCliente);
        $retornoCliente->execute();
        $regCliente = $retornoCliente->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($regCliente as $rgCli) {        
            echo "|".mb_str_pad($rgCli->id_cliente."-".$rgCli->nome_cliente, 27, " ")."|";            
            $limite ++;   

            if ($limite == 2) {
                echo "\n";
                $limite = 0;
            }
        }
        echo "\n|========================================================|\n";
        $id_cli = (int) readline("Informe o código do Cliente: ");

        echo "\n* Informações de Saída do Produto *\n";
        $lote_saida = (int) readline("Informe o número do Lote: "); 
        $qtd_saida = (int) readline("Informe a quantidade de saída do produto: ");        
        $valor_saida = (double) readline("Informe o valor de saída do produto: ");   

        
        if ($id_prod != 0 && $id_cli != 0 && $lote_saida != 0 && $qtd_saida != 0 && $valor_saida != 0) { 
            if ($qtd_saida <= $valida_qtd && $valida_qtd > 0) {
                $data_saida = date('d/m/Y H:i');

                $sqlSaida = "INSERT INTO item_saida VALUES (NULL, :lote, :quantidade, :valor, :data_saida, :id_produto, :id_cliente)";
                $dadosInsertSaida = $conexao->prepare($sqlSaida);
                $dadosInsertSaida->bindParam(':lote', $lote_saida);
                $dadosInsertSaida->bindParam(':quantidade', $qtd_saida);
                $dadosInsertSaida->bindParam(':valor', $valor_saida);
                $dadosInsertSaida->bindParam(':data_saida', $data_saida);
                $dadosInsertSaida->bindParam(':id_produto', $id_prod);            
                $dadosInsertSaida->bindParam(':id_cliente', $id_cli);            
                $retornoInsertSaida = $dadosInsertSaida->execute();
                
                if ($retornoInsertSaida) {
                    $sqlUpdProd = "UPDATE produto SET quantidade_prod = quantidade_prod - :quantidade_prod 
                                   WHERE id_produto = :id_produto";
                    $updateProd = $conexao->prepare($sqlUpdProd);                
                    $updateProd->bindParam(':quantidade_prod',$qtd_saida);
                    $updateProd->bindParam(':id_produto',$id_prod);
                    $retornoUpdSai = $updateProd->execute(); 

                    return retornoCadastro($retornoUpdSai, true); 
                }
            } else {
                echo "\nATENÇÃO!!!\nQuantidade do produto informado é maior que a quantidade no estoque.\n";
                echo "  (Digite 1) - Para iniciar a sáida do produto novamente.\n";
                echo "  Para voltar ao Menu precione a tecla Enter.\n";
                $opcao_saida = (int) readline("->");   
                
                if ($opcao_saida != 1) {
                    return false; 
                } 
            }
        } else {
            echo "\nATENÇÃO!!!\nÉ necessário preencher todas as informações.\n";
            echo "  (Digite 1) - Para iniciar a saída do produto novamente.\n";
            echo "  Para voltar ao Menu precione a tecla Enter.\n";
            $opcao_saida2 = (int) readline("->");   
            
            if ($opcao_saida2 != 1) {
                return false; 
            } 
        }        
    }   
}

function relatorioAtualEstoque() {    
    echo "==========================================================\n";
    echo "|                       Relatórios                       |\n";
    echo "==========================================================\n";
    echo "(Digite 1) Relatório Atual Total do Estoque.\n";
    echo "(Digite 2) Relatório Atual por Item Específico do Estoque.\n";
    $tipo_relatorio = (int) readline("->");

    if ($tipo_relatorio == RELAT_EST_TOTAL || $tipo_relatorio == RELAT_EST_ITEM) {
        relatorioEstoque($tipo_relatorio);
    }
}

function relatorioEstoque($opcao_relatorio) {
    include 'conexao/conexao.php';

    $relatorio = "";
    $desc_relatorio = "TOTAL DO ESTOQUE"; 
    if ($opcao_relatorio == RELAT_EST_ITEM) {              
        echo registroProduto(7, 48);        
        $id_prod = (int) readline("Informe o código do Produto: ");
        $desc_relatorio = "POR ITEM ESPECÍFICO DO ESTOQUE";
    }

    $sqlEstoque = "SELECT p.id_produto, p.descricao, p.peso, p.valor_prod, p.quantidade_prod, c.nome_categoria, f.nome_fornecedor 
                   FROM produto AS p 
                   INNER JOIN categoria AS c ON c.id_categoria = p.id_categoria
                   INNER JOIN fornecedor AS f ON f.id_fornecedor = p.id_fornecedor ";
    if ($opcao_relatorio == RELAT_EST_ITEM) {
        $sqlEstoque .= "WHERE p.id_produto = :id_produto";
    }
    $retornoEstoque = $conexao->prepare($sqlEstoque);
    if ($opcao_relatorio == RELAT_EST_ITEM) {
        $retornoEstoque->bindParam(':id_produto', $id_prod);
    }
    $retornoEstoque->execute();
    $regEstoque = $retornoEstoque->fetchAll(PDO::FETCH_OBJ);

    system('clear');
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= "|                                                                                     |\n";
    $relatorio .= "|".mb_str_pad("RELATÓRIO ATUAL ".$desc_relatorio, 85, " ", STR_PAD_BOTH)."|\n";
    $relatorio .= "|                                                                                     |\n";
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= registroLoja(1);
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= "|ID |Descrição do Produto       |Peso |Valor |Qtd |Categoria        |Fornecedor       |\n";
    $relatorio .= "|=====================================================================================|\n";
    if ($regEstoque) {
        foreach ($regEstoque as $rgEst) {
            $relatorio .= "|".mb_str_pad($rgEst->id_produto, 3, " ", STR_PAD_BOTH)."|".mb_str_pad($rgEst->descricao, 27, " ")
                         ."|".mb_str_pad($rgEst->peso, 5, " ", STR_PAD_LEFT)."|".mb_str_pad($rgEst->valor_prod, 6, " ", STR_PAD_LEFT)
                         ."|".mb_str_pad($rgEst->quantidade_prod, 4, " ", STR_PAD_BOTH)."|".mb_str_pad($rgEst->nome_categoria, 17, " ")
                         ."|".mb_str_pad($rgEst->nome_fornecedor, 17, " ")."|\n";            
        }   
    } else {
        $relatorio .= "|                         SEM REGISTRO(S) PARA APRESENTAR!!!                          |\n";
    }
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= "-> Relatório gerado: ".date('d/m/Y H:i')."\n\n\n";   
    echo $relatorio;

    echo "(Digite 1) Para salvar o Relatório\n";
    echo "Para voltar ao Menu precione a tecla Enter\n";     
    $salvar_rel = (int) readline("->");  

    if ($salvar_rel == 1) {           
        gerarRelatorio($opcao_relatorio, $relatorio);          
        retornoMsgRelatorio();        
    }
}   

function relatorioMovimentacaoEstoque() {
    echo "=========================================================\n";
    echo "|                       Relatórios                      |\n";
    echo "=========================================================\n";
    echo "(Digite 3) Relatório de Movimentação do Estoque por Item.\n";
    echo "(Digite 4) Relatório de Movimentação por Período de Data.\n";
    $tipo_rela = (int) readline("->");

    if ($tipo_rela == RELAT_MOV_ITEM || $tipo_rela == RELAT_MOV_DATA) {
        relatorioMovimentacao($tipo_rela);
    } 
}

function relatorioMovimentacao($opcao_relatorio) {
    include 'conexao/conexao.php';

    $relatorio = "";
    $linha = 0;
    $periodo = "";
    if ($opcao_relatorio == RELAT_MOV_ITEM) {        
        echo registroProduto(7, 47);        
        $id_prod = (int) readline("Informe o código do Produto: ");

        $desc_relatorio = "DE MOVIMENTAÇÃO DO ESTOQUE POR ITEM";
        $sqlWhere .= "WHERE id_prod = :id_prod "; 
    } else {
        echo "\n* Informe o período de data *\n";
        $data_mov_ini = readline("Data Inicial: ");         
        $data_mov_fim = readline("Data Final: ");
        
        $desc_relatorio = "DE MOVIMENTAÇÃO POR PERÍODO DE DATA";
        $sqlWhere .= "WHERE SUBSTR(data_mov, 1, 10) >= :data_mov_ini AND SUBSTR(data_mov, 1, 10) <= :data_mov_fim ";
        $periodo = "Período de Data: ".$data_mov_ini." à ".$data_mov_fim;
    }    

    $sqlMov = "SELECT item_ent.id_produto AS id_prod, p.descricao, item_ent.lote, item_ent.quantidade, item_ent.valor, 
               item_ent.data_entrada AS data_mov, '' AS nome_cliente, '' AS cpf, 'Entrada' AS tipo,
               forn.nome_fornecedor, forn.cnpj_forne 
               FROM item_entrada AS item_ent
               INNER JOIN produto AS p ON p.id_produto = item_ent.id_produto 
               INNER JOIN fornecedor AS forn ON forn.id_fornecedor = p.id_fornecedor ";
    $sqlMov .= $sqlWhere;
    $sqlMov .= "UNION ALL 
               SELECT item_sai.id_produto AS id_prod, p.descricao, item_sai.lote, item_sai.quantidade, item_sai.valor, 
               item_sai.data_saida AS data_mov, cli.nome_cliente, cli.cpf, 'Saída' AS tipo,
               forn.nome_fornecedor, forn.cnpj_forne 
               FROM item_saida AS item_sai
               INNER JOIN produto AS p ON p.id_produto = item_sai.id_produto
               INNER JOIN fornecedor AS forn ON forn.id_fornecedor = p.id_fornecedor
               INNER JOIN cliente AS cli ON cli.id_cliente = item_sai.id_cliente ";
    $sqlMov .= $sqlWhere;
    $sqlMov .= "ORDER BY id_prod, data_mov";
    $retornoMov = $conexao->prepare($sqlMov);

    if ($opcao_relatorio == RELAT_MOV_ITEM) {
        $retornoMov->bindParam(':id_prod', $id_prod);        
    } else {
        $retornoMov->bindParam(':data_mov_ini', $data_mov_ini);
        $retornoMov->bindParam(':data_mov_fim', $data_mov_fim);
    }
    $retornoMov->execute();
    $regMov = $retornoMov->fetchAll(PDO::FETCH_OBJ);

    system('clear');
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= "|                                                                                     |\n";
    $relatorio .= "|".mb_str_pad("RELATÓRIO ".$desc_relatorio, 85, " ", STR_PAD_BOTH)."|\n";    
    $relatorio .= "|".mb_str_pad($periodo, 85, " ", STR_PAD_BOTH)."|\n";
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= registroLoja(1);                                                                      
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= "|                                     REGISTRO(S)                                     |\n";
    $relatorio .= "|=====================================================================================|\n";
    if ($regMov) {
        foreach ($regMov as $rgMov) {
            $linha ++;
            $relatorio .= "|ID: ".mb_str_pad($rgMov->id_prod, 4, " ", STR_PAD_BOTH)." Descrição do Produto: ".mb_str_pad($rgMov->descricao, 26, " ")
                         ."Lote: ".mb_str_pad($rgMov->lote, 8, " ")."Valor: ".mb_str_pad($rgMov->valor, 7, " ")."|\n";
            $relatorio .= "|Qtd: ".mb_str_pad($rgMov->quantidade, 4, " ")."Data da Movimentação: ".mb_str_pad($rgMov->data_mov, 26, " ")
                         ."Tipo Movimentação: ".mb_str_pad($rgMov->tipo, 9, " ")."|\n";
            if ($rgMov->tipo == "Saída") {
                $relatorio .= "|Cliente: ".mb_str_pad($rgMov->nome_cliente, 48, " ")."CPF: ".mb_str_pad(mascaraCpfCnpj($rgMov->cpf), 23, " ")."|\n";
                $relatorio .= "|Fornecedor: ".mb_str_pad($rgMov->nome_fornecedor, 45, " ")."CNPJ: ".mb_str_pad(mascaraCpfCnpj($rgMov->cnpj_forne), 22, " ")."|\n";
            }
            if ($linha < count($regMov)) {
                $relatorio .= "|-------------------------------------------------------------------------------------|\n";
            }                
        }
    } else {
        $relatorio .= "|                         SEM REGISTRO(S) PARA APRESENTAR!!!                          |\n";
    }
    $relatorio .= "|=====================================================================================|\n";
    $relatorio .= "-> Relatório gerado: ".date('d/m/Y H:i')."\n\n\n";   
    echo $relatorio;   
    
    echo "(Digite 1) Para salvar o Relatório\n";
    echo "Para voltar ao Menu precione a tecla Enter\n";     
    $salvar_rel = (int) readline("->");  

    if ($salvar_rel == 1) {           
        gerarRelatorio($opcao_relatorio, $relatorio);  
        retornoMsgRelatorio();
    }    
}

function registroLoja($tipo_retorno) {
    include 'conexao/conexao.php';

    $retorno_echo = "";    
    $sqlLoja = "SELECT l.nome_loja, l.cnpj_loja, l.contato_loja, l.ie_loja, e.rua, e.numero,
                b.nome_bairro, b.cep_bairro, c.nome_cidade, est.uf_estado
                FROM loja AS l
                INNER JOIN endereco AS e ON e.id_endereco = l.id_endereco
                INNER JOIN bairro AS b ON b.id_bairro = e.id_bairro
                INNER JOIN cidade AS c ON c.id_cidade = b.id_cidade
                INNER JOIN estado AS est ON est.id_estado = c.id_estado";
    $retornoLoja = $conexao->prepare($sqlLoja);
    $retornoLoja->execute();
    $reg = $retornoLoja->fetchAll(PDO::FETCH_OBJ);

    foreach ($reg as $rgLoja) {
        $retorno_echo .= "|Nome da Loja: ".mb_str_pad($rgLoja->nome_loja, 18, " ")."CNPJ: ".mb_str_pad(mascaraCpfCnpj($rgLoja->cnpj_loja), 22, " ")
                        ."IE: ".mb_str_pad($rgLoja->ie_loja, 21, " ")."|\n";
        $retorno_echo .= "|Contato: ".mb_str_pad(mascaraTelefone($rgLoja->contato_loja), 23, " ")."Rua: ".mb_str_pad($rgLoja->rua, 23, " ")
                        ."N° ".mb_str_pad($rgLoja->numero, 6, " "). "Bairro: ".mb_str_pad($rgLoja->nome_bairro, 8, " ")."|\n";
        $retorno_echo .= "|CEP: ".mb_str_pad($rgLoja->cep_bairro, 27, " ")."Município: ".mb_str_pad($rgLoja->nome_cidade."/".$rgLoja->uf_estado, 42, " ")."|\n";
        $retorno_array = [
            "nome-loja" => $rgLoja->nome_loja,
            "cnpj" => mascaraCpfCnpj($rgLoja->cnpj_loja),
            "ie" => $rgLoja->ie_loja,
            "contato" => mascaraTelefone($rgLoja->contato_loja),
            "rua" => $rgLoja->rua,
            "numero" => $rgLoja->numero,
            "bairro" => $rgLoja->nome_bairro,
            "cep" => $rgLoja->cep_bairro,
            "municipio" => $rgLoja->nome_cidade."/".$rgLoja->uf_estado 
        ];
    }

    return ($tipo_retorno == 1 ? $retorno_echo : $retorno_array);
}

function registroProduto($lengthProd, $lengthDesc) {
    include 'conexao/conexao.php';

    $retorno = "";
    $sqlEst = "SELECT id_produto, descricao FROM produto";
    $retEst = $conexao->prepare($sqlEst);
    $retEst->execute();
    $regEst = $retEst->fetchAll((PDO::FETCH_OBJ));

    $retorno .= "\n|".mb_str_pad("=", $lengthProd + $lengthDesc + 1, "=")."|\n";
    $retorno .= "|Código |".mb_str_pad("Descrição", $lengthProd + $lengthDesc - 7, " ")."|\n";
    $retorno .= "|".mb_str_pad("=", $lengthProd + $lengthDesc + 1, "=")."|\n";
    foreach ($regEst as $rgEst) {
        $retorno .= "|".mb_str_pad($rgEst->id_produto, $lengthProd, " ", STR_PAD_BOTH)."|".mb_str_pad($rgEst->descricao, $lengthDesc, " ")."|\n";
    }
    $retorno .= "|".mb_str_pad("=", $lengthProd + $lengthDesc + 1, "=")."|\n";

    return $retorno;
}

function validaTerminais() {
    if (!is_dir("../papelaria/relatorios")) {
        mkdir("../papelaria/relatorios");
        if (!is_dir("../papelaria/relatorios/relatorio estoque")) {
            mkdir("../papelaria/relatorios/relatorio estoque");
            if ("../papelaria/relatorios/relatorio estoque/estoque total") {
                mkdir("../papelaria/relatorios/relatorio estoque/estoque total");
            }
            if ("../papelaria/relatorios/relatorio estoque/estoque item espefico") {
                mkdir("../papelaria/relatorios/relatorio estoque/estoque item espefico");
            }
        }
        if (!is_dir("../papelaria/relatorios/relatorio movimentacao estoque")) {
            mkdir("../papelaria/relatorios/relatorio movimentacao estoque");
            if (!is_dir("../papelaria/relatorios/relatorio movimentacao estoque/movimentacao item especifico")) {
                mkdir("../papelaria/relatorios/relatorio movimentacao estoque/movimentacao item especifico");
            }
            if (!is_dir("../papelaria/relatorios/relatorio movimentacao estoque/movimentacao por data")) {
                mkdir("../papelaria/relatorios/relatorio movimentacao estoque/movimentacao por data");
            }
        }
    }    
}

function gerarRelatorio($tipo_rel, $dados) {
    $gerado = date('d-m-Y H-i-s');

    if ($tipo_rel == RELAT_EST_TOTAL) {        
        $arq_grav = fopen("../papelaria/relatorios/relatorio estoque/estoque total/relatorio estoque-total ".$gerado.".txt", "a");      
    } elseif ($tipo_rel == RELAT_EST_ITEM) {        
        $arq_grav = fopen("../papelaria/relatorios/relatorio estoque/estoque item espefico/relatorio estoque-item especifico ".$gerado.".txt", "a");        
    } elseif ($tipo_rel == RELAT_MOV_ITEM) {
        $arq_grav = fopen("../papelaria/relatorios/relatorio movimentacao estoque/movimentacao item especifico/relatorio movimentacao-item especifico ".$gerado.".txt", "a");        
    } else {
        $arq_grav = fopen("../papelaria/relatorios/relatorio movimentacao estoque/movimentacao por data/relatorio movimentacao por data ".$gerado.".txt", "a");        
    }
    fwrite($arq_grav, $dados);
    fclose($arq_grav);      

    return sleep(1);
}

function retornoCadastro($sqlRetorno, $mensagem) {
    if ($sqlRetorno) {
        if ($mensagem) {
            echo "\n\nSUCESSO!!!\nCadastro realizado com sucesso.\n";
            readline("Para voltar ao Menu precione a tecla Enter");

            return false;   
        } 
        return true;                 
    } else {
        if ($mensagem) {
            echo "\n\nERRO!!!\nHouve um erro ao cadastrar.\n";
            readline("Para voltar ao Menu precione a tecla Enter"); 
        }
        return true;                                                    
    }    
}

function retornoMsgRelatorio() {
    echo "\nRelatório Criado com Sucesso!!!\n";
    echo "Aguarde, Voltando para Menu Principal.";
    return sleep(2.5);
}

function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = "UTF-8") {
    $diff = strlen($input) - mb_strlen($input, $encoding);
    return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
} 

function mascaraCpfCnpj($registro) {
    $mascara = "";

    if (!empty($registro)) {
        if (strlen(trim($registro)) > 11) {
            $mascara = substr($registro, 0, 2).".".substr($registro, 2, 3).".".substr($registro, 5, 3)."/".substr($registro, 8, 4)."-".substr($registro, 12, 2);
        } else {
            $mascara = substr($registro, 0, 3).".".substr($registro, 3, 3).".".substr($registro, 6, 3)."-".substr($registro, 9, 2);
        }
    }
    return $mascara;
}

function mascaraTelefone($contato) {
    $mascara_contato = "";
        
    if (strlen(trim($contato)) > 10) {
        $mascara_contato = "(".substr($contato, 0, 2).") ".substr($contato, 2, 9);
    } else {
        $mascara_contato = "(".substr($contato, 0, 2).") ".substr($contato, 2, 4)."-".substr($contato, 6, 4);
    }
    return $mascara_contato;
}
?>

#123456