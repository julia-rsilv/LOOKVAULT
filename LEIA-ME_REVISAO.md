# LOOKVAULT — versão revisada

Projeto de guarda-roupa digital feito em PHP, MySQL/MariaDB, HTML, CSS e JavaScript.

## O que já está incluído

- Cadastro, login e logout.
- Migração automática de senhas antigas em MD5 para `password_hash()` após login bem-sucedido.
- Guarda-roupa: cadastro, edição, exclusão e registro de uso das peças.
- Criação e organização de looks.
- Estatísticas protegidas por autenticação.
- Perfil/conta.
- Recuperação e redefinição de senha por token com validade de 1 hora (depende de e-mail configurado no servidor).
- Wishlist com inclusão e remoção de favoritos.
- Calendário de looks com agendamento e remoção.
- Marketplace com anúncios de troca/doação e atualização de status.
- Modo Viagem para registrar destino, datas e observações.
- Assistente de Estilo com sugestões baseadas nas peças cadastradas (regras locais; não é IA generativa).
- Central de Ferramentas para acessar os módulos.
- Banco atualizado com tabelas de favoritos, calendário, marketplace e viagens.

## Como instalar localmente

1. Instale XAMPP, Laragon ou outro ambiente com PHP 8+ e MySQL/MariaDB.
2. Copie a pasta `LOOKVAULT` para a pasta pública do servidor (por exemplo, `htdocs`).
3. Inicie Apache e MySQL/MariaDB.
4. Abra o phpMyAdmin e importe `database/lookvault_corrigido.sql`.
5. Confira as credenciais em `configdatabase.php`. Por padrão, usa `localhost`, `root`, senha vazia e banco `lookvault`.
6. Acesse `http://localhost/LOOKVAULT/`.

Também é possível configurar as variáveis de ambiente `LOOKVAULT_DB_HOST`, `LOOKVAULT_DB_USER`, `LOOKVAULT_DB_PASS` e `LOOKVAULT_DB_NAME`.

## Recuperação de senha

A aplicação gera token aleatório, armazena somente o hash e limita o link a uma hora. O envio usa `mail()` do PHP. Em ambiente local, normalmente é necessário configurar um serviço SMTP ou uma biblioteca como PHPMailer; sem isso, o e-mail não será entregue. O sistema mantém uma mensagem genérica para não revelar se um endereço está cadastrado.

## Limites que ainda dependem de integração

- O Assistente de Estilo é baseado em regras e nos dados locais; para IA generativa é necessário configurar uma API e uma chave privada no servidor.
- O Marketplace permite publicar e visualizar anúncios entre contas autenticadas, mas ainda não possui chat, proposta de troca, pagamento ou confirmação de entrega.
- O Modo Viagem registra o planejamento da viagem. A seleção de peças para a mala e checklist interativo são uma evolução futura.
- Não há integração meteorológica para recomendações por clima.
- O envio de recuperação de senha depende da configuração de e-mail/SMTP do ambiente.

## Verificações realizadas nesta revisão

- `php -l`: 41 arquivos PHP sem erros de sintaxe.
- `node --check`: `assets/js/main.js` e `assets/js/theme.js` sem erros.
- Conferência estática das referências às tabelas: todas as tabelas usadas pelo PHP estão presentes no SQL.
- Corrigida a chave inválida da tabela `uso_pecas` que referenciava colunas inexistentes.
- Não foi possível executar teste integrado com MySQL/MariaDB nesta sessão, pois não há servidor/cliente de banco disponível no ambiente. Importe o SQL e teste os fluxos no XAMPP/Laragon.
