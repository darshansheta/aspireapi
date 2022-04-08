# Aspire API

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
- Admin can approve a loan. On approval system will create repayments with status `unpaid` 
- After loan is approved user can repay loan
    - To repay loan , user mast pay on or before next scheduled time
    - amount must ve greater than or equal to next up coming repayment
    - user can only maximum total remaining_amount which can be found in loan details
    - If user pay more than next repayment then remaining extra amount get credited in up coming repayment and that repayment will be marked as `partial_paid`
    - once all repayments are paid then automatically marked as `paid`