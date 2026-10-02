# Segurança

Este repositório não deve conter credenciais reais de banco de dados, chaves de API, tokens ou dados pessoais de usuários.

## Antes de publicar em produção

- Execute a instalação com um banco de dados próprio.
- Troque as credenciais temporárias exibidas pelo instalador.
- Mantenha `config/database.local.php` fora do Git.
- Remova ou restrinja o acesso público ao `install.php` após concluir a instalação.
- Use HTTPS.
- Revise permissões de arquivos e pastas na hospedagem.
- Cadastre apenas profissionais e conteúdos reais após a devida validação.
- Não use dados reais de crianças no ambiente de demonstração.

## Relato de vulnerabilidades

Se este projeto for publicado publicamente, prefira receber relatos de segurança de forma privada em vez de abrir detalhes sensíveis em issues públicas.
