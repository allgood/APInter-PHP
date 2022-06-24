APInter-PHP
===========

Projeto com o início de uma biblioteca para a utilização das API fornecidas pelo Banco Inter.

Inicialmente apenas a criação de boletos é suportada.

⚠️ ATENÇÃO
----------

O Banco Inter está encerrando o suporte à primeira versão da API, já não permitindo mais a emissão de chaves e certificados para uso dela, como os certificados expiram após um ano, em breve ninguém mais conseguirá acessá-la.

A branch com o nome "master" está sendo descontinuada a partir desse ponto, pull requests não serão aceitos e apenas erros muito críticos serão resolvidos nessa branch.

A nova branch terá o nome de "main" e será a principal, a versão do pacote do composer vai para a versão 2.

Como usar:
----------

### Instalação

Para utilizar a biblioteca através do composer:

```
composer require ctodobom/api-inter:1.0.2
```

### Documentação 

O arquivo [exemplo.php](exemplo.php) fornece o básico para a utilização das classes.

Os parâmetros para a execução do exemplo devem ser salvos no arquivo com o nome `.env`, exemplos de configuração encontram-se no arquivo `.env.example`

> **ATENÇÃO:**
>
> Todos os dados verificáveis precisam ser válidos Utilize sempre CPF/CNPJ, CEP, Cidade e Estado válidos Para evitar importunar estranhos utilize seus próprios dados ou de alguma pessoa que esteja ciente, pois as cobranças sempre são cadastradas no sistema quente do banco central e aparecerão no DDA dos sacados. Os dados de exemplo NÃO SÃO VÁLIDOS e se não forem alterados o script de exemplo não funcionará.

Licença
-------

Todo o código deste projeto está licensiado sob a GNU Lesser General Public License versão 3.

Pode ser utilizado inalterado em qualquer projeto fechado ou open source, alterações efetuadas precisam ser fornecidas em código aberto aos usuários do sistema.

Facilitou sua vida?
-------------------

Se o código do projeto ajudou você em uma tarefa complexa, considere fazer uma doação ao autor pelo PIX abaixo.

![image](https://user-images.githubusercontent.com/6070736/116247400-317e3680-a741-11eb-9434-9f226eec39b5.png)

Chave Pix: 80fd8916-1131-4844-917e-2732eaa2ba74

Propaganda do Autor:
--------------------

Não relacionado à essa biblioteca diretamente, mas fazendo uso da mesma API, desenvolvi um app para Android que emite e gerencia boletos do Banco Inter. A aplicação é de código fechado e com o objetivo de ser completamente grátis, mantida por publicidade.

[Para instalar é só clicar aqui!](https://play.google.com/store/apps/details?id=dev.todobom.interbill)
