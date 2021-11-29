## About Laravel Idempotency Handler

Using this package you can block duplicate/concurrent requests for performing a specific action based on a specific time.

### How to use?
- Publish the idempotency config with `php artisan vendor: publish --tag=idempotency`.
- You must configure this file based on each route that you have in your project.
- Set the `Idempotency` middleware on the route you want.

