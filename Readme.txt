Overview
This API allows users to create, read, update, and delete blog posts. Below are the details of the endpoints and how to interact with them using Postman.

Endpoints
1. Create a Post
Description: This endpoint allows you to create a new blog post.
Method: POST
URL: http://localhost/apiTask/api.php/posts
jsonCopy code{ "title": "abc", "content": "sdffffffffffffffffffffffffffffff", "author": "Falguni Thakor"}
Response: Returns the newly created post with its ID.

2. Update a Post
Description: This endpoint allows you to update an existing blog post.
Method: PUT
URL: http://localhost/apiTask/api.php/posts/{id}
Replace {id} with the ID of the post you want to update.

jsonCopy code{ "title": "abcdef", "content": "Post Content", "author": "Falguni"}
Response: Returns the updated post.

3. Get All Posts
Description: This endpoint retrieves all blog posts.
Method: GET
URL: http://localhost/apiTask/api.php/posts
Response: A JSON array of all posts, each with its id, title, content, author, created_at, and updated_at.

4. Get Post by ID
Description: This endpoint retrieves a specific blog post by its ID.
Method: GET
URL: http://localhost/apiTask/api.php/posts/{id}
Replace {id} with the ID of the post you want to retrieve.

Response: The post with the given id, or a 404 if not found.

5. Delete a Post
Description: This endpoint deletes a post by its ID.
Method: DELETE
URL: http://localhost/apiTask/api.php/posts/{id}
Replace {id} with the ID of the post you want to delete.

Response: A confirmation message or a 404 if the post is not found.

EndFragment

6.Serach and Pagination

Description: This endpoint will serch post based on title ,auther and content and also for pagination.
Method: GET
URL: http://localhost/apiTask/api.php/posts?search=EFG&limit=1&offset=1



