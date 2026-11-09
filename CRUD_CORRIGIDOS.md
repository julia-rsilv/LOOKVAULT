# CRUDs corrigidos do LOOKVAULT

Arquivos:
- `crud_create.php`: valida campos e valor, usa prepared statement e limita tamanhos.
- `crud_read.php`: lista somente peças do usuário autenticado e escapa saída HTML.
- `crud_update.php`: valida ID, propriedade e dados antes de atualizar.
- `crud_delete.php`: exige POST e verifica propriedade do registro.
- `configdatabase.php`: conexão UTF-8 completa e helper de queries corrigido.

Importação do banco:
1. Abra phpMyAdmin.
2. Importe `database/lookvault_corrigido.sql` (ou o SQL standalone).
3. Confira as credenciais em `configdatabase.php`.
4. Coloque os arquivos PHP no diretório do projeto.

O dump mantém os registros que estavam no SQL fornecido e adiciona `CREATE DATABASE/USE` e uma chave única para impedir que a mesma peça seja vinculada duas vezes ao mesmo look.
