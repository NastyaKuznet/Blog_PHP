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