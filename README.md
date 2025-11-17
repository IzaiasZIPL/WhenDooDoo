When Doodoo

Um aplicativo simples e funcional de To-Do List desenvolvido em PHP, utilizando MySQL, HTML, CSS e JavaScript.
O objetivo do projeto é permitir que o usuário crie, edite, acompanhe o progresso e exclua tarefas de forma rápida e intuitiva.

Funcionalidades

Adicionar novas tarefas

Editar tarefas existentes

Atualizar o progresso da tarefa (0% a 100%)

Excluir tarefas

Interface simples e responsiva

Organização visual com barra de progresso

Estrutura modular para facilitar manutenção e expansão

Estrutura do Projeto
When-Doodoo/
│── index.php
│── README.md
│
├── actions/
│   ├── create.php
│   ├── update.php
│   ├── update-progress.php
│   └── delete.php
│
├── database/
│   └── conn.php
│
├── src/
│   ├── styles/style.css
│   ├── javascript/script.js
│   └── images/
│       ├── background.jpg
│       └── bg.png

Tecnologias Utilizadas

PHP 8+

MySQL / MariaDB

HTML5

CSS3

JavaScript (Vanilla)

XAMPP ou WAMP para execução local

Como Executar o Projeto Localmente
1. Clone o repositório
git clone https://github.com/seuusuario/when-doodoo.git
cd when-doodoo

2. Configure o banco de dados

Crie o banco no MySQL:

CREATE DATABASE todolist;


Crie a tabela necessária:

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    progress INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

3. Configure a conexão

Edite o arquivo database/conn.php com as informações do seu servidor local:

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "todolist";

4. Inicie o servidor

Coloque o projeto na pasta htdocs (caso use XAMPP):

htdocs/when-doodoo/


Acesse no navegador:

http://localhost/when-doodoo/
