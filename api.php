<?php
header('Content-Type: application/json');
require 'config.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$segments = explode('/', $uri);

if (count($segments) < 3 || $segments[1] !== 'api.php' || $segments[2] !== 'posts') {
    http_response_code(404);
    echo json_encode(['message' => 'Endpoint not found']);
    exit;
}

$postId = isset($segments[3]) ? (int)$segments[3] : null;

switch ($requestMethod) {
    case 'GET':
        if ($postId) {
            // Get Single Post
            getPost($postId);
        } else {
            // Get All Posts
            getPosts();
        }
        break;

    case 'POST':
        // Create a Post
        createPost();
        break;

    case 'PUT':
        if ($postId) {
            // Update a Post
            updatePost($postId);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Post ID required']);
        }
        break;

    case 'DELETE':
        if ($postId) {
            // Delete a Post
            deletePost($postId);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Post ID required']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

function getPosts() {
    global $pdo;

    // Retrieve and sanitize query parameters
    $search = isset($_GET['search']) ? '%' . htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') . '%' : '%';
    $author = isset($_GET['author']) ? '%' . htmlspecialchars($_GET['author'], ENT_QUOTES, 'UTF-8') . '%' : '%';
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10; 
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0; 

    // Prepare the SQL query with search, pagination, and descending order
    $sql = "SELECT * FROM posts 
            WHERE (title LIKE ? OR content LIKE ?) 
              AND author LIKE ? 
            ORDER BY id DESC 
            LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindValue(1, $search, PDO::PARAM_STR);
    $stmt->bindValue(2, $search, PDO::PARAM_STR);
    $stmt->bindValue(3, $author, PDO::PARAM_STR);
    $stmt->bindValue(4, $limit, PDO::PARAM_INT);
    $stmt->bindValue(5, $offset, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the posts as a JSON response
        echo json_encode($posts);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to retrieve posts', 'error' => $e->getMessage()]);
    }
}


function getPost($id) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($post) {
        echo json_encode($post);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Post not found']);
    }
}

function createPost() {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['title'], $input['content'], $input['author'])) {
        $title = htmlspecialchars($input['title'], ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars($input['content'], ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars($input['author'], ENT_QUOTES, 'UTF-8');

        $stmt = $pdo->prepare('INSERT INTO posts (title, content, author) VALUES (?, ?, ?)');

        if ($stmt->execute([$title, $content, $author])) {
            $id = $pdo->lastInsertId();
            $post = [
                'id' => $id,
                'title' => $title,
                'content' => $content,
                'author' => $author,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            echo json_encode(["message" => "Post created successfully", "post" => $post]);
            http_response_code(201);
        } else {
            echo json_encode(["error" => "Failed to create post"]);
            http_response_code(400);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid input']);
    }
}

function updatePost($id) {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);
    $fields = [];
    $values = [];
    
    foreach ($input as $key => $value) {
        if (in_array($key, ['title', 'content', 'author'])) {
            $fields[] = "$key = ?";
            $sanitized_value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $values[] = $sanitized_value;
        }
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['message' => 'No fields to update']);
        return;
    }
    
    $values[] = $id;

    try {
        $stmt = $pdo->prepare('UPDATE posts SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($values);

        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            echo json_encode(["message" => "Post updated successfully", "post" => $post]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Post not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to update post', 'error' => $e->getMessage()]);
    }
}

function deletePost($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount()) {
        echo json_encode(['message' => 'Post deleted']);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Post not found']);
    }
}
?>
