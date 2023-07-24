# Welcome to Snowtricks 👋
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#)
[![Twitter: tomcdj71](https://img.shields.io/twitter/follow/tomcdj71.svg?style=social)](https://twitter.com/tomcdj71)

> 6th project of my OpenClassrooms courses

## Pre-requisites :
- PHP 8.1
- Composer
- npm/yarn (I used pnpm)
- Symfony CLI
---

## Install

```sh
git clone https://github.com/tomcdj71-openclassrooms/BileMo
cd Snowtricks
composer install --optimize-autoloader
yarn install --force
yarn build
symfony console d:d:c
symfony console d:m:m
symfony console d:f:l
symfony serve
```

## Features

- [] Api Endpoint 'products list' (/products);
- [] Api Endpoint 'product details' (/products/:prodcutId);
- [] Api Endpoint 'client users list' (/clients/:clienId/users)
- [] Api Endpoint 'client user detail' (/clients/:clientId/users/:userId)
- [] Api Endpoint 'client new user' (/clients/:clientId/users)
- [] Api Endpoint 'client delete user' (/clients/:clientId/users)
- [] Api Documentation
- [] Api Authentication (JWT)

## Usage

Once you've ran `symfony serve` you can open your browser and go to [http://localhost:8000/api](http://localhost:8000/api) and start using the app

## About this project

This project was made with [Symfony 6.3 ](https://symfony.com/releases/6.3) and [PHP 8.2.7](https://www.php.net/ChangeLog-8.php#8.2.7). 

## Author

👤 **Thomas Chauveau**

* Twitter: [@tomcdj71](https://twitter.com/tomcdj71)
* Github: [@tomcdj71](https://github.com/tomcdj71)

## Show your support

Give a ⭐️ if this project helped you!


***
_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_
