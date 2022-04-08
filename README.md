# Aspire API

## Notes
- This application is built using laravel 9 and PHP version 8.
- API authentication is handled by Laravel sanctum
- I have created `IsAdmin` middleware to handle admin APIs
- To validate request I rely heavily on `FormRequest` class, so you will find all validation logic there
- Used `Policy` class to handle authorization for Loan model
- I prefer clean controller so I do not put application logic there instead I create a Service class (a pure class) that I always resolve using DI to use anywhere in application
- My service class contains business logic and reusable functions
- I also prefer to have separate layer for API resource, so for major case I use Laravel resources
- Since I have user PHP 8 I tried to type hint argument and return type for major controller, models and service class. Also, I have used new `Enum` class for Loan and Repayment's status column
- Since we have to deal with amount so keep it accurate I have used Money package to avoid rounding errors
- I have created Feature testcases, Those covers all positive testcases and major negative testcases
- As per code challenge, I am asked to use `pending` status for repayments model, but I found `unpaid` more meaning full, so I made choice there 

## Installation and setup guide
- Clone repository using this command `git clone https://github.com/darshansheta/aspireapi.git`.
- Install all dependencies via composer using following command `composer install`
- Create .env from .env.example file `cp .env.example .env` and create database in mysql and set database details like name, username and password in .env
- Set application key by running following command `php artisan key:generate`
- Run all migration `php artisan migrate`
- Start local server `php artisan serve`
- Create database named `aspire_api_test` or adjust `DB_DATABASE` value in `phpunit.xml` for test database.
- Run testcases `php artisan test`

## API list
- POST Register `/api/register`
- GET Login `/api/login`
- POST Request a loan `/api/loans`
- GET List user's all loan `/api/loans`
- GET Show user's specific loan `/api/loans/{loan}`
- PUT Approve a loan by admin `/api/loans/{loan}/approve`
- POST Repay a loan `/api/loans/{loan}/repay`

## Postman API collection

[**Api collection**](https://www.getpostman.com/collections/0792b13185d24279b6c0)

## Features
- User can register and login .
- User can request loan with term and amount that user want , in system loan with status `pending` will be created.
- User can see their list of loans and specific loan details.
- Admin can approve a loan. On approval system will create N number of repayments with status `unpaid`  where N is equal loan term.
- After loan is approved user can repay loan
    - To repay loan , user mast pay on or before next scheduled time
    - amount must ve greater than or equal to next up coming repayment
    - user can only maximum total remaining_amount which can be found in loan details
    - If user pay more than next repayment then remaining extra amount get credited in up coming repayment and that repayment will be marked as `partial_paid`
    - once all repayments are paid then automatically marked as `paid`


## Following packages are used
* `moneyphp/money` -  Money for PHP: To handle money related operation like, divid, calculate remaining and partial paid amount
* `laravel/sanctum` - Laravel Sanctum: To handle api authentication