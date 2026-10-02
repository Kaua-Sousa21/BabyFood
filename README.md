# Baby Food

Plataforma web responsiva em **PHP + MySQL** voltada à venda avulsa de marmitas infantis, conteúdos de alimentação, receitas, vídeos e comunicação entre responsáveis e nutricionistas pelo próprio sistema.

> Projeto em estágio de MVP. Os conteúdos alimentares presentes no banco inicial são demonstrativos e devem ser revisados por profissionais habilitados antes de qualquer uso comercial.

## Funcionalidades

### Responsável
- Cadastro e login.
- Cadastro de perfis das crianças.
- Faixas etárias: **6–8 meses, 8–10 meses, 10–12 meses e 1–3 anos**.
- Catálogo de marmitas por faixa etária.
- Carrinho e checkout.
- Consulta automática de CEP com preenchimento de cidade/UF e, quando disponível, logradouro.
- Histórico de pedidos.
- Receitas e vídeos por faixa etária.
- Conteúdos e dicas de nutrição.
- Chat interno com nutricionistas.
- Notificações.

### Nutricionista
- Dashboard próprio.
- Conversas com responsáveis.
- Histórico do chat.
- Cadastro/edição de receitas e vídeos conforme permissões do sistema.
- Perfil profissional.

### Administrador
- Dashboard administrativo.
- Gerenciamento de marmitas.
- Gerenciamento de receitas e vídeos.
- Gerenciamento de conteúdos educativos.
- Cadastro de nutricionistas.
- Gerenciamento de pedidos.
- Gerenciamento dos principais conteúdos da plataforma.

## Visual

A interface utiliza uma identidade própria do Baby Food, com tipografia **Fraunces + Plus Jakarta Sans**, paleta em verde profundo, creme, menta e pêssego, microinterações, transições, animações de entrada, navegação responsiva e suporte a `prefers-reduced-motion`.

## Requisitos

- PHP 8.0 ou superior.
- MySQL 5.7+/8.0+ ou MariaDB compatível.
- Extensão `pdo_mysql` habilitada.
- Apache recomendado para uso das regras `.htaccess`.
- Acesso à internet no navegador para a consulta de CEP pelo ViaCEP e carregamento das fontes externas utilizadas pela interface.

## Instalação rápida

1. Clone ou baixe este repositório.
2. Crie um banco MySQL vazio.
3. Coloque o projeto no `htdocs` do XAMPP ou na pasta pública da sua hospedagem.
4. Acesse `install.php` pelo navegador.
5. Informe host, porta, banco, usuário e senha do MySQL.
6. O instalador cria as tabelas, os dados demonstrativos e o arquivo local `config/database.local.php`.
7. Guarde as credenciais temporárias exibidas ao fim da instalação e altere-as antes de publicar o sistema.
8. Em produção, remova ou bloqueie o acesso ao `install.php` depois da instalação.

### XAMPP

Exemplo:

```text
C:/xampp/htdocs/baby-food/
```

Depois acesse:

```text
http://localhost/baby-food/install.php
```

## Configuração manual do banco

Se não quiser usar o instalador, copie:

```text
config/database.local.example.php
```

para:

```text
config/database.local.php
```

E altere as credenciais localmente. O arquivo `database.local.php` está no `.gitignore` e **não deve ser enviado ao GitHub**.

O esquema principal está em:

```text
database/schema.sql
```

## Estrutura do projeto

```text
baby-food/
├── actions/          # Ações POST/AJAX
├── app/              # Bootstrap, autenticação, banco e helpers
├── assets/
│   ├── css/
│   ├── img/
│   └── js/
├── config/           # Configurações da aplicação
├── database/         # Schema e dados demonstrativos
├── pages/
│   ├── admin/
│   ├── auth/
│   └── nutritionist/
├── partials/         # Header, footer e componentes compartilhados
├── uploads/          # Uploads locais; conteúdo gerado é ignorado pelo Git
├── .gitignore
├── .htaccess
├── index.php
└── install.php
```

## Consulta de CEP

O checkout utiliza o **ViaCEP** para consultar um CEP brasileiro com 8 dígitos e preencher automaticamente cidade, UF e, quando disponível, logradouro. Caso a consulta externa não funcione, os campos permanecem editáveis para preenchimento manual.

## Pagamentos

O checkout atual registra o pedido e apresenta opções de pagamento na interface, mas **não realiza cobrança financeira real**. Para produção, integre um gateway de pagamento e nunca armazene dados completos de cartão no Baby Food.

## Vídeos

Os vídeos podem ser cadastrados usando uma URL incorporável, por exemplo:

```text
https://www.youtube.com/embed/ID_DO_VIDEO
```

Isso evita armazenar arquivos MP4 grandes diretamente na hospedagem.

## Segurança

O projeto inclui medidas básicas como:

- PDO com prepared statements.
- `password_hash()` e `password_verify()`.
- Tokens CSRF.
- Escape de HTML.
- Controle de acesso por perfil.
- Bloqueio de execução de PHP na pasta de uploads via `.htaccess`.
- Bloqueio web das pastas internas `app`, `config` e `database` em servidores Apache compatíveis.
- Arquivos locais e credenciais ignorados pelo Git.

Leia também [`SECURITY.md`](SECURITY.md).

## Antes de colocar em produção

- Troque todas as credenciais temporárias.
- Use HTTPS.
- Configure backups do banco.
- Revise permissões da hospedagem.
- Integre um gateway de pagamentos adequado.
- Revise receitas, ingredientes, alergênicos, texturas, porções e conteúdos com profissionais habilitados.
- Revise requisitos de privacidade e proteção de dados, especialmente por envolver informações relacionadas a crianças.

## Publicando no GitHub

Depois de extrair o projeto:

```bash
git init
git add .
git commit -m "feat: versão inicial do Baby Food"
git branch -M main
git remote add origin URL_DO_SEU_REPOSITORIO
git push -u origin main
```

O `.gitignore` já está configurado para evitar o envio de credenciais locais, arquivos de instalação gerados, uploads e arquivos temporários.

## Licença

Nenhuma licença de código aberto foi definida ainda. Adicione uma licença somente quando decidir quais permissões deseja conceder a terceiros para uso, modificação e distribuição do projeto.
