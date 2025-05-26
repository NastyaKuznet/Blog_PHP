-- name: getAllPosts
SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id
WHERE p.is_publish = true and p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id;

-- name: getPostsByAuthorAlphabetical
SELECT p.* , 
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id  
JOIN users u2 ON p.last_editor_id = u2.id
WHERE p.is_publish = true and p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id
ORDER BY u.login ASC;

-- name: getPostsByAuthorReverseAlphabetical
SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id
WHERE p.is_publish = true and p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id
ORDER BY u.login DESC;

-- name: getPostsByAuthor
SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id 
WHERE u.login = :author_login AND p.is_publish = true AND p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id;

-- name: getPostsByLikesAscending
SELECT p.*, 
       u.login as user_login,
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id 
WHERE p.is_publish = true and p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id
ORDER BY like_count ASC;

-- name: getPostsByLikesDescending
SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
JOIN users u ON p.author_id = u.id  
JOIN users u2 ON p.last_editor_id = u2.id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
WHERE p.is_publish = true and p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id
ORDER BY like_count DESC;

-- name: getPostsByCommentsAscending
SELECT p.*, 
       u.login as user_login,
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id
WHERE p.is_publish = true and p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id
ORDER BY comment_count ASC;

-- name: getPostsByCommentsDescending
SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id 
JOIN users u2 ON p.last_editor_id = u2.id 
WHERE p.is_publish = true and p.is_delete = false
GROUP BY p.id, u.login, u2.login, ca.id
ORDER BY comment_count DESC;

-- name: getPostsByTag
SELECT p.* ,
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM tags t 
JOIN posts p ON t.post_id = p.id
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u2 ON p.last_editor_id = u2.id 
JOIN users u ON p.author_id = u.id  
WHERE t.name = :tag_name
GROUP BY p.id, u.login, u2.login, ca.id;

-- name: getPostsByUserId
SELECT p.*, 
       u.login as user_login,
       u2.login as last_editor_login, 
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id  
JOIN users u2 ON p.last_editor_id = u2.id
WHERE u.id = :user_id and p.is_publish = true
GROUP BY p.id, u.login, u2.login, ca.id;

-- name: getAllNonPublishPosts
SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login
FROM posts p
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id
WHERE p.is_publish = false and p.is_delete = false
GROUP BY p.id, u.login, u2.login;

-- name: getPostById (post select)
SELECT * FROM posts WHERE id = :postId;

-- name: getPostById (like count)
SELECT COUNT(*) FROM likes WHERE post_id = :postId;

-- name: getPostById (comment count)
SELECT COUNT(*) FROM comments WHERE post_id = :postId AND is_delete = false;

-- name: getPostById (author login)
SELECT login FROM users WHERE id = :authorId;

-- name: getPostById (editor login)
SELECT login FROM users WHERE id = :lastEditorId;

-- name: getPostById (category data)
SELECT category_id FROM category_posts WHERE post_id = :post_id;

-- name: getPostById (category name)
SELECT name FROM categories WHERE id = :category_id;

-- name: getNonPublishPostById (post select)
SELECT * FROM posts WHERE id = :postId;

-- name: getNonPublishPostById (author login)
SELECT login FROM users WHERE id = :authorId;

-- name: getNonPublishPostById (editor login)
SELECT login FROM users WHERE id = :lastEditorId;

-- name: getNonPublishPostById (category data)
SELECT category_id FROM category_posts WHERE post_id = :post_id;

-- name: getNonPublishPostById (category name)
SELECT name FROM categories WHERE id = :category_id;

-- name: getCountPostsByUserId
SELECT COUNT(*) FROM posts p WHERE p.author_id = :user_id and p.is_publish = true;

-- name: addPost
INSERT INTO posts (title, preview, content, author_id, last_editor_id) VALUES (:title, :preview, :content, :author_id, :author_id);

-- name: editPost
UPDATE posts 
SET title = :title, 
    preview = :preview,
    content = :content,
    edit_date = CURRENT_TIMESTAMP,
    last_editor_id = :editor_id
WHERE id = :post_id;

-- name: deletePost
UPDATE posts 
SET delete_date = CURRENT_TIMESTAMP, 
    is_delete = true
WHERE id = :post_id;

-- name: publishPost
UPDATE posts 
SET publish_date = CURRENT_TIMESTAMP, 
    is_publish = true
WHERE id = :post_id;

-- name: getCommentsByPostId
SELECT c.*, u.login as user_login
FROM comments c
JOIN users u ON c.user_id = u.id
WHERE c.post_id = :post_id AND c.is_delete = FALSE;

-- name: getCommentById
SELECT * FROM comments WHERE id = :id AND is_delete = FALSE;

-- name: addComment
INSERT INTO comments (content, post_id, user_id, created_date, is_edit, is_delete) 
VALUES (:content, :post_id, :user_id, CURRENT_TIMESTAMP, FALSE, FALSE);

-- name: updateComment
UPDATE comments
SET content = :content,
    edit_date = CURRENT_TIMESTAMP,
    is_edit = TRUE
WHERE id = :comment_id;

-- name: deleteComment
UPDATE comments
SET is_delete = TRUE,
    delete_date = CURRENT_TIMESTAMP
WHERE id = :comment_id;

-- name: checkLikeByPostIdAndUserId
SELECT * FROM likes l WHERE l.post_id = :post_id AND l.user_id = :user_id;

-- name: addLike
INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id);

-- name: deleteLike
DELETE FROM likes l WHERE l.post_id = :post_id AND l.user_id = :user_id;

-- name: getUserInfo
SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :user_id;

-- name: getAllUsers
SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC;

-- name: changeUserRole
UPDATE users SET role_id = :new_role_id WHERE id = :user_id;

-- name: deleteUser (exists check)
SELECT COUNT(*) FROM users WHERE id = ?;

-- name: deleteUser (get post IDs)
SELECT id FROM posts WHERE user_id = ?;

-- name: deleteUser (delete comments)
DELETE FROM comments WHERE post_id IN ($placeholders);

-- name: deleteUser (delete posts)
DELETE FROM posts WHERE user_id = ?;

-- name: deleteUser (delete user)
DELETE FROM users WHERE id = ?;

-- name: addUser
INSERT INTO users (login, password, role_id) VALUES (:login, :password, 2);

-- name: authorizationUser
SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.login = :login;

-- name: checkUserLogin
SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.login = :login;

-- name: toggleUserBan
UPDATE users SET is_banned = ? WHERE id = ?;

-- name: getRoles
SELECT r.id, r.name FROM roles r;

-- name: getAllTags
SELECT * FROM tags;

-- name: getTagsByPostId
SELECT t.* FROM tags t WHERE t.post_id = :post_id;

-- name: getAllCategories
SELECT * FROM categories WHERE is_delete = FALSE ORDER BY id ASC;

-- name: addTag
INSERT INTO tags (name, post_id) VALUES (:name, :post_id);

-- name: deleteTag
DELETE FROM tags t WHERE t.name = :name AND t.post_id = :post_id;

-- name: getCategoryById
SELECT * FROM categories WHERE id = :category_id AND is_delete = FALSE;

-- name: addCategory
INSERT INTO categories (name, parent_id) VALUES (:name, :parent_id);

-- name: getTagIdByName
SELECT id FROM tags WHERE name = :name LIMIT 1;

-- name: addPostAndGetId
INSERT INTO posts (title, preview, content, author_id, last_editor_id) VALUES (:title, :preview, :content, :user_id, :user_id) RETURNING id;

-- name: getCategoriesByPostId
SELECT c.* FROM categories c JOIN category_posts cp ON c.id = cp.category_id WHERE cp.post_id = :post_id AND c.is_delete = FALSE;

-- name: getPostsByCategoryId
WITH RECURSIVE category_tree AS (
    SELECT id FROM categories WHERE id = :category_id AND is_delete = FALSE
    UNION ALL
    SELECT c.id 
    FROM categories c
    INNER JOIN category_tree ct ON c.parent_id = ct.id
    WHERE c.is_delete = FALSE
)
SELECT p.*, 
       u.login as user_login,
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,  
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
JOIN category_posts cp ON p.id = cp.post_id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id
WHERE cp.category_id IN (SELECT id FROM category_tree) 
  AND p.is_publish = true
  AND p.is_delete = false
  AND ca.is_delete = FALSE
GROUP BY p.id, u.login, u2.login, ca.id;

-- name: deleteCategory (markAsDeletedRecursive)
UPDATE categories SET is_delete = TRUE WHERE id = :category_id AND is_delete = FALSE;

-- name: deleteCategory (get children)
SELECT id FROM categories WHERE parent_id = :category_id AND is_delete = FALSE;

-- name: connectPostAndCategory (check category)
SELECT 1 FROM categories WHERE id = :category_id AND is_delete = FALSE;

-- name: connectPostAndCategory (delete existing)
DELETE FROM category_posts WHERE post_id = :post_id;

-- name: connectPostAndCategory (insert new)
INSERT INTO category_posts (category_id, post_id) VALUES (:category_id, :post_id);