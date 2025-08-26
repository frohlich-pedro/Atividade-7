# Sistema de Gerenciamento de Biblioteca

Este é um sistema CRUD completo para gerenciamento de uma biblioteca, desenvolvido em PHP com XAMPP.

## Funcionalidades

- Cadastro, listagem, edição e exclusão de autores, livros, leitores e empréstimos
- Filtros e paginação nas listagens
- Validações de regras de negócio
- Interface simples e intuitiva

## Estrutura do Banco de Dados

O banco de dados possui 4 tabelas:

1. **autores**: id_autor, nome, nacionalidade, ano_nascimento
2. **livros**: id_livro, titulo, genero, ano_publicacao, id_autor (FK)
3. **leitores**: id_leitor, nome, email, telefone
4. **emprestimos**: id_emprestimo, id_livro (FK), id_leitor (FK), data_emprestimo, data_devolucao

## Regras de Negócio Implementadas

1. Um autor pode ter vários livros, mas um livro pertence a apenas um autor
2. Um livro só pode ser emprestado se não houver outro empréstimo ativo (sem data de devolução)
3. O ano de publicação do livro deve ser maior que 1500 e menor ou igual ao ano atual
4. A data de devolução não pode ser anterior à data de empréstimo
5. Cada leitor pode ter no máximo 3 empréstimos ativos ao mesmo tempo

## Instalação

1. Instale o XAMPP e inicie o Apache e MySQL
2. Clone ou copie os arquivos para a pasta `htdocs` do XAMPP
3. Acesse o phpMyAdmin e crie o banco de dados executando o SQL fornecido
4. Acesse o sistema através de `http://localhost/biblioteca`

## Uso

- Navegue pelo menu para acessar as diferentes funcionalidades
- Use os filtros para encontrar registros específicos
- Utilize a paginação para navegar entre grandes conjuntos de dados

## Tecnologias Utilizadas

- PHP
- MySQL
- HTML
- CSS
- XAMPP

## Estrutura de Arquivos
