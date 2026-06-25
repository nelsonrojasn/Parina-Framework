# Implantação

Mantenha os arquivos de acesso público separados do código da aplicação para garantir uma instalação segura.

**Passos recomendados**

1. Copie os arquivos públicos para o diretório raiz de documentos do seu servidor web (exemplo: `/var/www/html` ou a raiz de um subdomínio):

```bash
cp -R public/* /var/www/html/
```

*Nota: Certifique-se de copiar também o arquivo oculto `.htaccess`. Em alguns sistemas operacionais ou configurações de terminal, as cópias com curingas como `public/*` podem omitir arquivos ocultos. Você pode copiá-lo explicitamente:*

```bash
cp public/.htaccess /var/www/html/
```

2. Copie o restante dos arquivos do projeto para fora do site público (exemplo: `/var/www/parina`):

```bash
mkdir -p /var/www/parina
# A partir da raiz do repositório do projeto; exclui a pasta public
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Instale as dependências do PHP com o Composer na raiz do projeto. Certifique-se de que o Composer esteja instalado e execute o comando como o proprietário do projeto (não como `root`). Para produção, use as flags recomendadas abaixo para ignorar pacotes de desenvolvimento e otimizar o autoloader.

```bash
# mude para a raiz do projeto (onde o composer.json está localizado)
cd /var/www/parina
# desenvolvimento: instala todas as dependências
composer install
# produção: ignora pacotes de desenvolvimento e otimiza o autoloader
composer install --no-dev --optimize-autoloader
```

4. Dê ao grupo do Apache (servidor web) a propriedade da pasta do banco de dados para que o servidor possa ler/escrever conforme necessário (exemplo para Debian/Ubuntu):

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Notas:
- Se preferir, em vez de copiar os arquivos, você pode apontar o DocumentRoot do seu host virtual para a pasta `public` dentro do seu projeto (por exemplo, `/var/www/parina/public`).
- Ajuste os comandos e os nomes de usuário/grupo para corresponder à sua distribuição e configuração de hospedagem.
- Proteja qualquer arquivo de ambiente ou configuração (não os exponha dentro do diretório raiz web público).

Atenciosamente
