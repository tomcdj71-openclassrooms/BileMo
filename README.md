# Welcome to BileMo 👋
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#)
[![Twitter: tomcdj71](https://img.shields.io/twitter/follow/tomcdj71.svg?style=social)](https://twitter.com/tomcdj71)

> 7th project of the PHP / Symfony course on OpenClassrooms: develop the API of a mobile phone sales site

## Pre-requisites :
- PHP 8.1
- Composer
- Symfony CLI
---

## Install

```sh
git clone https://github.com/tomcdj71-openclassrooms/BileMo
cd BileMo
composer install --optimize-autoloader
symfony console d:d:c
symfony console d:m:m
symfony console d:f:l
symfony console lexik:jwt:generate-keypair
symfony serve
```

## Features
- [] Api Authentication (JWT)
- [] Api for retrieving products of BileMo and customers of BileMo customers

## API Endpoints
### Products

- **List all products**

  `GET /products`

  Get a list of all products.

- **Product details**

  `GET /products/:productId`

  Get details of a specific product identified by `productId`.

### Client Users

- **List all client users**

  `GET /users`

  Get a list of all users (customers) belonging to the logged-in client.

- **Client user detail**

  `GET /users/:userId`

  Get details of a specific user (customer) belonging to the logged-in client.

- **Create a new client user**

  `POST /users`

  Create a new user (customer) for the logged-in client.

- **Delete a client user**

  `DELETE /users/:userId`

  Delete a user (customer) belonging to the logged-in client.

### API Documentation

- **API Documentation**

  `GET /docs`

  Access the API documentation to explore available endpoints and usage details.

## Usage

Once you've ran `symfony serve` you can open your browser and go to [http://localhost:8000/api](http://localhost:8000/api) and start using the app

If you want to test the app : 
```json
username: company0@company.com
password: password
```

## About this project

This project was made with [Symfony 6.3 ](https://symfony.com/releases/6.3) and [PHP 8.2.7](https://www.php.net/ChangeLog-8.php#8.2.7). 

A Postman collection is also [available here](.postman_collection/BileMo.json) if you want to test the api into Postman.

## Author

👤 **Thomas Chauveau**

* Twitter: [@tomcdj71](https://twitter.com/tomcdj71)
* Github: [@tomcdj71](https://github.com/tomcdj71)

## Show your support

Give a ⭐️ if this project helped you!


***
_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_
