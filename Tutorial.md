# Introduction #

In this introductory tutorial, we will be building an administration area, which allows the creation and management of sports and tournaments.
The purpose of this tutorial is to cover some of the basic concepts, such as CRUD operations along with an overview of some of the basic controllers and filters, which help speed up the development of web applications, by abstracting common operations such as session management and user authentication.

## Controllers ##

All basic controllers of Edge are located under Edge/Controllers.
Every controller you create needs to (directly or indirectly) inherit from BaseController.

One of the most useful controllers, for admin sections, is the AuthController. A common use case is that you create a Base class for all your admin controllers, which extends the AuthController, where you implement the abstract methods of AuthController and you are done. Every admin page is protected after that.

For our application we will create the below controllers, under the directory Application/Controllers.

Login -> Handles the rendering of the login page, along with the authentication.

BaseAdminController -> Base controller for all admin controllers, which extends AuthController.

Event -> Handles CRUD operations for events

Tournament -> Handles CRUD operations for tournaments