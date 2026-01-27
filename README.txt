PARA UMA INSTALAÇÃO LIMPA FAÇA O DOWNLOAD DOS ARQUIVOS SEM A PASTA vendor ASSIM TEMOS MAIS CERTEZA DE QUE TUDO FUNCIONARÁ PERFEITAMENTE NA SUA MAQUINA!!!

RODE OS COMANDOS NO POWERSHELL PARA UMA INSTALAÇÃO LIMPA:

cd C:\laragon\www
git clone SEU_REPO sempag
cd sempag

copy .env.example .env

composer install

php artisan key:generate

php artisan migrate --force

php artisan db:seed --force

///////////////////////////

UTILIZANDO O LARAGON BASTA INICAR O APACHE E O MYSQL E NO POWER SHELL RODAR:

php artisan serve

SE CASO TIVER PROBLEMAS COM IMAGENS NÃO CARREGANDO É POR CONTA DE PERMISSÕES EM PASTAS NORMALMENTE GERA UM ERRO 403 RODE ESSE COMANDO NO POWERSHELL:


if (Test-Path "public\storage") { Remove-Item "public\storage" -Recurse -Force }
cmd /c mklink /J public\storage storage\app\public

///////////////////////////


ASSIM PODENDO ACESSAR O SISTEMA EM:

http://127.0.0.1:8000/e/demo20252/
http://127.0.0.1:8000/e/demo20252/admin