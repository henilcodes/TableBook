<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class RestaurantRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM restaurants ORDER BY name");
        return $stmt->fetchAll();
    }
    
    public function findBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM restaurants WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM restaurants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getHours($restaurantId)
    {
        $stmt = $this->db->prepare("SELECT * FROM restaurant_hours WHERE restaurant_id = ? ORDER BY day_of_week");
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    }

    public function replaceHours(int $restaurantId, array $hours): void
    {
        $this->db->beginTransaction();
        try {
            $deleteStmt = $this->db->prepare("DELETE FROM restaurant_hours WHERE restaurant_id = ?");
            $deleteStmt->execute([$restaurantId]);

            $insertStmt = $this->db->prepare("
                INSERT INTO restaurant_hours (restaurant_id, day_of_week, open_time, close_time, is_closed)
                VALUES (:restaurant_id, :day_of_week, :open_time, :close_time, :is_closed)
            ");

            foreach ($hours as $hour) {
                $insertStmt->execute([
                    ':restaurant_id' => $restaurantId,
                    ':day_of_week' => (int)$hour['day_of_week'],
                    ':open_time' => $hour['open_time'],
                    ':close_time' => $hour['close_time'],
                    ':is_closed' => !empty($hour['is_closed']) ? 1 : 0,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function ensureDefaultHours(int $restaurantId): void
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM restaurant_hours WHERE restaurant_id = ?");
        $stmt->execute([$restaurantId]);
        $count = (int)$stmt->fetchColumn();
        if ($count > 0) {
            return;
        }

        $defaultHours = [];
        for ($day = 0; $day <= 6; $day++) {
            $defaultHours[] = [
                'day_of_week' => $day,
                'open_time' => '11:00:00',
                'close_time' => '22:00:00',
                'is_closed' => false,
            ];
        }
        $this->replaceHours($restaurantId, $defaultHours);
    }
    
    public function getTables($restaurantId)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, ts.name as section_name 
            FROM tables t 
            LEFT JOIN table_sections ts ON t.section_id = ts.id 
            WHERE t.restaurant_id = ? AND t.is_active = TRUE 
            ORDER BY t.sort_order, t.table_number
        ");
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    }
    
    public function getMenuCategories($restaurantId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM menu_categories 
            WHERE restaurant_id = ? 
            ORDER BY display_order, name
        ");
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    }
    
    public function getMenuItems($restaurantId, $categoryId = null)
    {
        if ($categoryId) {
            $stmt = $this->db->prepare("
                SELECT * FROM menu_items 
                WHERE restaurant_id = ? AND category_id = ? AND is_available = TRUE 
                ORDER BY display_order, name
            ");
            $stmt->execute([$restaurantId, $categoryId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT * FROM menu_items 
                WHERE restaurant_id = ? AND is_available = TRUE 
                ORDER BY category_id, display_order, name
            ");
            $stmt->execute([$restaurantId]);
        }
        return $stmt->fetchAll();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM restaurants WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM restaurants WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        return (bool)$stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO restaurants (name, slug, description, cuisine_type, address, phone, email, image_url, rating)
            VALUES (:name, :slug, :description, :cuisine_type, :address, :phone, :email, :image_url, :rating)
        ");

        $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'] ?? null,
            ':cuisine_type' => $data['cuisine_type'] ?? null,
            ':address' => $data['address'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':rating' => $data['rating'] ?? 0.00,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE restaurants
            SET name = :name,
                slug = :slug,
                description = :description,
                cuisine_type = :cuisine_type,
                address = :address,
                phone = :phone,
                email = :email,
                image_url = :image_url,
                rating = :rating
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'] ?? null,
            ':cuisine_type' => $data['cuisine_type'] ?? null,
            ':address' => $data['address'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':rating' => $data['rating'] ?? 0.00,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM restaurants WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Admin: get all tables (including inactive) for a restaurant.
     */
    public function getAllTablesAdmin(int $restaurantId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, ts.name as section_name 
            FROM tables t 
            LEFT JOIN table_sections ts ON t.section_id = ts.id 
            WHERE t.restaurant_id = ?
            ORDER BY t.sort_order, t.table_number
        ");
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    }

    public function findTableById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, ts.name as section_name 
            FROM tables t 
            LEFT JOIN table_sections ts ON t.section_id = ts.id 
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getSections(int $restaurantId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM table_sections
            WHERE restaurant_id = ?
            ORDER BY name
        ");
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    }

    public function findOrCreateSection(int $restaurantId, string $name): ?int
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT id FROM table_sections
            WHERE restaurant_id = ? AND name = ?
            LIMIT 1
        ");
        $stmt->execute([$restaurantId, $trimmed]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }

        $insert = $this->db->prepare("
            INSERT INTO table_sections (restaurant_id, name)
            VALUES (?, ?)
        ");
        $insert->execute([$restaurantId, $trimmed]);
        return (int)$this->db->lastInsertId();
    }

    public function createTable(int $restaurantId, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO tables (restaurant_id, section_id, table_number, capacity, min_party_size, max_party_size, seating_preference, sort_order, notes, is_active)
            VALUES (:restaurant_id, :section_id, :table_number, :capacity, :min_party_size, :max_party_size, :seating_preference, :sort_order, :notes, :is_active)
        ");

        $stmt->execute([
            ':restaurant_id' => $restaurantId,
            ':section_id' => $data['section_id'],
            ':table_number' => $data['table_number'],
            ':capacity' => $data['capacity'],
            ':min_party_size' => $data['min_party_size'] ?? 1,
            ':max_party_size' => $data['max_party_size'] ?? null,
            ':seating_preference' => $data['seating_preference'],
            ':sort_order' => $data['sort_order'] ?? 0,
            ':notes' => $data['notes'] ?? null,
            ':is_active' => $data['is_active'] ? 1 : 0,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateTable(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tables
            SET section_id = :section_id,
                table_number = :table_number,
                capacity = :capacity,
                min_party_size = :min_party_size,
                max_party_size = :max_party_size,
                seating_preference = :seating_preference,
                sort_order = :sort_order,
                notes = :notes,
                is_active = :is_active
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':section_id' => $data['section_id'],
            ':table_number' => $data['table_number'],
            ':capacity' => $data['capacity'],
            ':min_party_size' => $data['min_party_size'] ?? 1,
            ':max_party_size' => $data['max_party_size'] ?? null,
            ':seating_preference' => $data['seating_preference'],
            ':sort_order' => $data['sort_order'] ?? 0,
            ':notes' => $data['notes'] ?? null,
            ':is_active' => $data['is_active'] ? 1 : 0,
        ]);
    }

    public function deleteTable(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tables WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Menu category & item management (admin)

    public function findCategoryById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM menu_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createCategory(int $restaurantId, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO menu_categories (restaurant_id, name, description, image_url, is_active, display_order)
            VALUES (:restaurant_id, :name, :description, :image_url, :is_active, :display_order)
        ");

        $stmt->execute([
            ':restaurant_id' => $restaurantId,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':is_active' => !empty($data['is_active']) ? 1 : 0,
            ':display_order' => $data['display_order'] ?? 0,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateCategory(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE menu_categories
            SET name = :name,
                description = :description,
                image_url = :image_url,
                is_active = :is_active,
                display_order = :display_order
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':is_active' => !empty($data['is_active']) ? 1 : 0,
            ':display_order' => $data['display_order'] ?? 0,
        ]);
    }

    public function deleteCategory(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM menu_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findMenuItemById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllMenuItemsAdmin(int $restaurantId): array
    {
        $stmt = $this->db->prepare("
            SELECT mi.*, mc.name as category_name
            FROM menu_items mi
            LEFT JOIN menu_categories mc ON mi.category_id = mc.id
            WHERE mi.restaurant_id = ?
            ORDER BY mc.display_order, mc.name, mi.display_order, mi.name
        ");
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    }

    public function createMenuItem(int $restaurantId, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO menu_items (
                restaurant_id, category_id, sku, name, description, price, image_url, prep_time_minutes, calories, spice_level,
                is_vegetarian, is_vegan, is_gluten_free, is_available, display_order
            )
            VALUES (
                :restaurant_id, :category_id, :sku, :name, :description, :price, :image_url, :prep_time_minutes, :calories, :spice_level,
                :is_vegetarian, :is_vegan, :is_gluten_free, :is_available, :display_order
            )
        ");

        $stmt->execute([
            ':restaurant_id' => $restaurantId,
            ':category_id' => $data['category_id'] ?: null,
            ':sku' => $data['sku'] ?? null,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':image_url' => $data['image_url'] ?? null,
            ':prep_time_minutes' => $data['prep_time_minutes'] ?? 15,
            ':calories' => $data['calories'] ?? null,
            ':spice_level' => $data['spice_level'] ?? 'none',
            ':is_vegetarian' => !empty($data['is_vegetarian']) ? 1 : 0,
            ':is_vegan' => !empty($data['is_vegan']) ? 1 : 0,
            ':is_gluten_free' => !empty($data['is_gluten_free']) ? 1 : 0,
            ':is_available' => $data['is_available'] ? 1 : 0,
            ':display_order' => $data['display_order'] ?? 0,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateMenuItem(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE menu_items
            SET category_id = :category_id,
                sku = :sku,
                name = :name,
                description = :description,
                price = :price,
                image_url = :image_url,
                prep_time_minutes = :prep_time_minutes,
                calories = :calories,
                spice_level = :spice_level,
                is_vegetarian = :is_vegetarian,
                is_vegan = :is_vegan,
                is_gluten_free = :is_gluten_free,
                is_available = :is_available,
                display_order = :display_order
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':category_id' => $data['category_id'] ?: null,
            ':sku' => $data['sku'] ?? null,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':image_url' => $data['image_url'] ?? null,
            ':prep_time_minutes' => $data['prep_time_minutes'] ?? 15,
            ':calories' => $data['calories'] ?? null,
            ':spice_level' => $data['spice_level'] ?? 'none',
            ':is_vegetarian' => !empty($data['is_vegetarian']) ? 1 : 0,
            ':is_vegan' => !empty($data['is_vegan']) ? 1 : 0,
            ':is_gluten_free' => !empty($data['is_gluten_free']) ? 1 : 0,
            ':is_available' => $data['is_available'] ? 1 : 0,
            ':display_order' => $data['display_order'] ?? 0,
        ]);
    }

    public function deleteMenuItem(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
