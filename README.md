# NFT Marketplace (versão básica em PHP, CSS e JavaScript)

Versão simplificada do "Desafio Frontend - Marketplace de NFTs" para rodar no localhost.
Sem React, sem banco de dados: os dados ficam em arquivos JSON na pasta `data/`.

## Como rodar

- **PHP embutido:** na pasta do projeto, rode `php -S localhost:8000` e abra http://localhost:8000
- **XAMPP:** copie a pasta para `htdocs` e abra http://localhost/nft-marketplace
- A pasta `data/` precisa ter permissão de escrita. Para resetar tudo, apague os `.json` dentro dela.

## Usuários de teste

ana@demo.com e leo@demo.com, senha `123456` (ou crie uma conta em `auth.php?mode=register`).

## O que existe

- Catálogo: busca, filtros combináveis, ordenação e paginação pela URL (`index.php`)
- Detalhe do NFT: edições disponíveis, quantidade, favoritos, NFT inexistente (`nft.php`)
- Favoritos com atualização otimista e rollback em caso de erro (`fav.php` + `assets/app.js`)
- Carrinho: quantidades, remoção, cupom, resumo; fica salvo e junta com o carrinho de visitante no login (`cart.php`)
- Cupons: `WELCOME10`, `GAMER20`, `OLD5` (expirado)
- Pagamento: dados, carteira, rede, revisão e confirmação; botão duplo clique e reenvio não duplicam pedido (chave de idempotência); se preço/estoque mudar entre revisão e confirmação, pede nova revisão (`checkout.php`)
- Recibo guarda um snapshot do pedido; só os itens comprados saem do carrinho
- Login, cadastro (e-mail duplicado), logout, senha com `password_hash` (`auth.php`)
- Para simular pagamento recusado: use uma carteira que termine em `dead` (ex.: `0x1234dead`)
- Valores em ETH são calculados com inteiros (gwei), sem erro de ponto flutuante

## Ficou de fora desta versão

Perfil/avatar, tela de carteiras cadastradas, tempo real (Socket.IO), MSW, testes Playwright, Lighthouse e skeletons.
