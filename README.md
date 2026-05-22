# Organizador

Aplicação web simples para gerenciar documentos, quadros e tarefas (CRUD), escrita em PHP com armazenamento em MySQL/MariaDB e estilos gerados via Tailwind CSS.

**Resumo**:

- **Linguagem:** PHP (com PDO)
- **Banco:** MySQL / MariaDB
- **Front-end:** HTML/CSS com Tailwind (opcionalmente rebuild via `npm`)

**Principais arquivos**:

- `index.php`: ponto de entrada / roteador simples.
- `app/config.php`: leitura de variáveis de ambiente (`.env`) e validação.
- `app/db.php`: criação da conexão PDO.
- `scripts/reset-database.php`: script CLI para recriar o esquema e (opcional) popular dados demonstrativos.
- `package.json`: scripts para compilar/watch do CSS via Tailwind.

**Requisitos**

- PHP 8+ (com extensão PDO e drivers MySQL habilitados)
- MySQL ou MariaDB
- Node.js + npm (apenas se quiser rebuildar o CSS)

Instalação e execução local

1. Clone o repositório e vá para a pasta do projeto:

```powershell
cd Organizador
```

2. Crie o arquivo `.env` na raiz do projeto com as credenciais do banco:

```
MYSQL_HOST=127.0.0.1
MYSQL_DATABASE=organizador
MYSQL_USER=seu_usuario
MYSQL_PASSWORD=sua_senha
```

3. (Opcional) Recrie o esquema do banco usando o script CLI (ATENÇÃO: apaga dados existentes):

```powershell
php scripts/reset-database.php --yes
# para também inserir dados demonstrativos:
php scripts/reset-database.php --yes --demo
```

4. Inicie o servidor PHP embutido (para desenvolvimento):

```powershell
php -S 127.0.0.1:8000
```

Abra `http://127.0.0.1:8000` no navegador. Se for a primeira execução e não houver usuários, a aplicação redirecionará para a página de setup para criar o usuário administrador.

Build dos estilos (opcional)

- O projeto já inclui `style.css` gerado; se quiser recompilar ou alterar as classes Tailwind:

```powershell
npm install
npm run build:css    # gera uma versão minificada em ./style.css
# ou para desenvolvimento com watch:
npm run watch:css
```

Observações sobre configuração

- O arquivo `app/config.php` procura por um `.env` na raiz e também lê variáveis de ambiente do sistema. Se faltar alguma variável requerida, a aplicação exibirá a página de `config-error` indicando quais chaves faltam.
- Se ocorrer erro de conexão, verifique `MYSQL_HOST`, `MYSQL_USER`, `MYSQL_PASSWORD` e `MYSQL_DATABASE`.

Uso do script de reset de banco

- Executar `php scripts/reset-database.php --yes` conecta ao banco configurado e remove/cria tabelas conforme `app/schema.php`.
- Use `--demo` para popular com conteúdo de exemplo.

Deploy em produção (breve)

- Configure um virtual host no Apache/Nginx apontando para a raiz do projeto e assegure que o `index.php` seja o front controller.
- Defina as mesmas variáveis do `.env` no ambiente (variáveis de sistema) ou mantenha um `.env` seguro fora do controle de versão.
- Use um usuário de banco com permissões apropriadas; não use root.

Resolução de problemas

- Página de erro de configuração: verifique `.env` e permissões de leitura.
- Erro de upload de arquivo: limite atual ~5 MB (ver mensagem ao tentar enviar).
- Problemas com CSS: verifique se `style.css` existe na raiz ou gere-o com `npm run build:css`.

Contribuição

- Pull requests são bem-vindos. Para mudanças no estilo, execute `npm run build:css` antes de abrir PR.

Licença

- (Adicionar licença se aplicável)

---

Se quiser, eu posso: atualizar um `.env.example` no repositório, adicionar instruções de deploy mais detalhadas para Apache/Nginx, ou commitar o `README` por você.
