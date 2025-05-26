UPDATE posts 
SET delete_date = CURRENT_TIMESTAMP, 
    is_delete = true
WHERE id = :post_id;