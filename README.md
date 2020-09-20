# Full Stack Developer Test

1. Create Mysql\MariaDB Database with 2 tables, users and posts.\
    a. Users table columns:\
        i. id\
        ii. name\
        iii. email\
        iv. updated_at\
        v. created_at\
    b. Posts table columns:\
        i. id\
        ii. user_id\
        iii. title\
        iv. body\
        v. updated_at\
        vi. created_at
2. Create 3 Classes\
    a. DbConnection class - write database connection class\
    b. User class with at least 1 method:\
        i. create - store a new user in your database\
    c. Post class with at least 4 methods:\
        i. searchById(post_id) - return the post by id if exists in your database\
        ii. searchByUserId(user_id) - return all posts that belong to the user if\
        exists in your database\
        iii. searchByContent(string) - return all the matching posts that contain\
        the given string in the post body or title in your database\
        iv. create - store a new post in your database
3. Fetch users and posts via ​ **curl** ​ and save them in your database (save only the\
    necessary values).\
    a. Users - ​https://jsonplaceholder.typicode.com/users \
    b. Posts - ​https://jsonplaceholder.typicode.com/posts
4. Create html file that will include a form, with the following fields\
    a. User’s name\
    b. User’s email\
    c. Post’s title\
    d. Post’s body\
    After submitting the form a new user and post should be created
5. Create php file that will output all the posts as json, the page should accept the next\
    get quires\
    a. post_id - output the posts with the given post id\
    b. user _id - output all the posts that created by the given user

6. Write a mysql query that will return the average of posts users created by monthly,\
    and weekly.\
    The columns should be: user_id, monthly_average, weekly_average.