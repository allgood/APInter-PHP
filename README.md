APInter-PHP
===========

Projeto com o início de uma biblioteca para a utilização das API fornecidas pelo Banco Inter.

Inicialmente apenas a criação de boletos é suportada.

Como usar:
----------

### Instalação

Para utilizar a biblioteca através do composer:

```
composer require ctodobom/api-inter
```

### Documentação 

O arquivo [exemplo.php](exemplo.php) fornece o básico para a utilização das classes.


> **ATENÇÃO:**
>
> Todos os dados verificáveis precisam ser válidos Utilize sempre CPF/CNPJ, CEP, Cidade e Estado válidos Para evitar importunar estranhos utilize seus próprios dados ou de alguma pessoa que esteja ciente, pois as cobranças sempre são cadastradas no sistema quente do banco central e aparecerão no DDA dos sacados. Os dados de exemplo NÃO SÃO VÁLIDOS e se não forem alterados o script de exemplo não funcionará.

Também está disponível a [documentação gerada a partir dos fontes](https://ctodobom.github.io/APInter-PHP/index.html).

Licença
-------

Todo o código deste projeto está licensiado sob a GNU Lesser General Public License versão 3.

Pode ser utilizado inalterado em qualquer projeto fechado ou open source, alterações efetuadas precisam ser fornecidas em código aberto aos usuários do sistema.
