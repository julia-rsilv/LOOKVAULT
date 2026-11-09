# LOOKVAULT

Organize seu guarda-roupa, monte combinações e acompanhe suas peças em um só lugar.

<p align="center">
  <img src="uploads/logo.png" alt="Logo LOOKVAULT" width="180">
</p>

## Sobre o projeto

O LOOKVAULT é uma aplicação web de moda e organização pessoal. Com uma conta, você pode cadastrar suas roupas e acessórios, criar looks e consultar informações sobre o uso do seu acervo.

## Funcionalidades

- **Conta pessoal:** cadastro, login, perfil e recuperação de senha.
- **Guarda-roupa digital:** cadastro, edição e remoção de peças, com informações como categoria, marca, cor e foto.
- **Looks:** crie combinações com as peças cadastradas e mantenha seus looks salvos.
- **Estatísticas:** acompanhe dados e atividades do seu guarda-roupa.
- **Ferramentas de estilo:** módulos de favoritos, calendário de looks, marketplace e planejamento de viagem.
- **Assistente de estilo:** apresenta sugestões baseadas nas peças cadastradas. As sugestões usam regras locais; não é uma IA generativa.

Alguns recursos dependem de configuração adicional do ambiente. Por exemplo, a recuperação de senha precisa de um serviço de e-mail configurado.

## Tecnologias

- PHP 8 ou superior
- MySQL ou MariaDB
- HTML, CSS e JavaScript
- Apache para executar localmente com XAMPP ou similar

## Como executar localmente

1. Instale e inicie o **Apache** e o **MySQL** usando XAMPP, Laragon ou uma configuração equivalente.
2. Clone este repositório para a pasta pública do servidor web. No XAMPP, ela costuma ser `htdocs`:

   ```bash
   git clone https://github.com/julia-rsilv/LOOKVAULT.git
   ```

3. Crie um banco de dados MySQL/MariaDB chamado `lookvault` (ou escolha outro nome).
4. Configure a conexão do banco pelas variáveis de ambiente:

   | Variável | Valor padrão |
   | --- | --- |
   | `LOOKVAULT_DB_HOST` | `localhost` |
   | `LOOKVAULT_DB_USER` | `root` |
   | `LOOKVAULT_DB_PASS` | vazio |
   | `LOOKVAULT_DB_NAME` | `lookvault` |

   O PHP também precisa da extensão **MySQLi** habilitada.

5. Prepare no banco as tabelas necessárias para a aplicação. **Um dump de banco não é distribuído neste repositório**, pois os arquivos locais de SQL podem conter registros de usuários. Use um esquema limpo e não importe dados pessoais de terceiros.
6. Abra `http://localhost/LOOKVAULT/` no navegador.

> Os valores padrão da conexão são adequados apenas para ambientes locais de desenvolvimento. Em produção, configure credenciais próprias e mantenha-as fora do controle de versão.

## Estrutura do projeto

```text
LOOKVAULT/
├── assets/
│   ├── css/          # Estilos da aplicação
│   └── js/           # Scripts do navegador
├── includes/         # Cabeçalho, autenticação e componentes compartilhados
├── uploads/
│   ├── logo.png      # Logo do projeto
│   └── pecas/        # Imagens de exemplo usadas pela interface
├── *.php             # Páginas e operações da aplicação
└── configdatabase.php
```

Fotos pessoais e dumps SQL não fazem parte do repositório. As pastas de uploads podem ser preenchidas localmente conforme o uso da aplicação.

## Observações

- A recuperação de senha usa o recurso de e-mail do PHP; normalmente é necessário configurar SMTP no ambiente.
- O assistente de estilo não chama um serviço externo de IA.
- O marketplace não processa pagamentos nem oferece chat entre usuários.
- Fontes, ícones e algumas imagens da página inicial podem ser carregados de serviços externos.

## Privacidade e segurança

Não publique senhas, tokens, dumps com dados reais nem fotos pessoais. Configure credenciais por variáveis de ambiente e revise os arquivos antes de enviar alterações ao GitHub.
