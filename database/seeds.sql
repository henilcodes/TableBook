-- TableTap Reservation System Seed Data

USE tabletap;

-- Admin User
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@tabletap.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password

-- Demo Customers
INSERT INTO customers (name, email, phone, password_hash, email_verified) VALUES
('John Doe', 'john@example.com', '555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE),
('Jane Smith', 'jane@example.com', '555-0102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE),
('Bob Johnson', 'bob@example.com', '555-0103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
-- Password for all: password

-- Demo Restaurant
INSERT INTO restaurants (name, slug, description, cuisine_type, address, phone, email, image_url, rating) VALUES
('Bella Vista', 'bella-vista', 'An elegant Italian restaurant offering authentic cuisine in a warm, inviting atmosphere. Perfect for romantic dinners and family gatherings.', 'Italian', '123 Main Street, City, State 12345', '555-1000', 'info@bellavista.com', 'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1200&q=80', 4.5);

SET @restaurant_id = LAST_INSERT_ID();

-- Restaurant Hours (Open Mon-Sat 11am-10pm, Sun 12pm-9pm)
INSERT INTO restaurant_hours (restaurant_id, day_of_week, open_time, close_time, is_closed) VALUES
(@restaurant_id, 0, '12:00:00', '21:00:00', FALSE), -- Sunday
(@restaurant_id, 1, '11:00:00', '22:00:00', FALSE), -- Monday
(@restaurant_id, 2, '11:00:00', '22:00:00', FALSE), -- Tuesday
(@restaurant_id, 3, '11:00:00', '22:00:00', FALSE), -- Wednesday
(@restaurant_id, 4, '11:00:00', '22:00:00', FALSE), -- Thursday
(@restaurant_id, 5, '11:00:00', '22:00:00', FALSE), -- Friday
(@restaurant_id, 6, '11:00:00', '22:00:00', FALSE); -- Saturday

-- Table Sections
INSERT INTO table_sections (restaurant_id, name, description, display_order, is_active) VALUES
(@restaurant_id, 'Main Dining', 'Spacious main dining area with elegant ambiance', 1, TRUE),
(@restaurant_id, 'Window Seating', 'Tables by the window with natural light', 2, TRUE),
(@restaurant_id, 'Outdoor Patio', 'Covered outdoor patio seating', 3, TRUE),
(@restaurant_id, 'Bar Area', 'Casual bar seating', 4, TRUE);

SET @section_main = (SELECT id FROM table_sections WHERE restaurant_id = @restaurant_id AND name = 'Main Dining' LIMIT 1);
SET @section_window = (SELECT id FROM table_sections WHERE restaurant_id = @restaurant_id AND name = 'Window Seating' LIMIT 1);
SET @section_outdoor = (SELECT id FROM table_sections WHERE restaurant_id = @restaurant_id AND name = 'Outdoor Patio' LIMIT 1);
SET @section_bar = (SELECT id FROM table_sections WHERE restaurant_id = @restaurant_id AND name = 'Bar Area' LIMIT 1);

-- Tables (12 tables total)
INSERT INTO tables (
    restaurant_id, section_id, table_number, capacity, min_party_size, max_party_size,
    seating_preference, sort_order, notes, is_active
) VALUES
(@restaurant_id, @section_main, 'T1', 2, 1, 2, 'indoor', 1, 'Quiet corner table', TRUE),
(@restaurant_id, @section_main, 'T2', 4, 2, 4, 'indoor', 2, 'Near central aisle', TRUE),
(@restaurant_id, @section_main, 'T3', 4, 2, 4, 'indoor', 3, NULL, TRUE),
(@restaurant_id, @section_main, 'T4', 6, 3, 6, 'indoor', 4, 'Preferred for family seating', TRUE),
(@restaurant_id, @section_window, 'T5', 2, 1, 2, 'window', 1, 'Best sunset view', TRUE),
(@restaurant_id, @section_window, 'T6', 4, 2, 4, 'window', 2, NULL, TRUE),
(@restaurant_id, @section_window, 'T7', 4, 2, 4, 'window', 3, NULL, TRUE),
(@restaurant_id, @section_outdoor, 'T8', 2, 1, 2, 'outdoor', 1, 'Patio heater nearby', TRUE),
(@restaurant_id, @section_outdoor, 'T9', 4, 2, 4, 'outdoor', 2, NULL, TRUE),
(@restaurant_id, @section_outdoor, 'T10', 6, 3, 6, 'outdoor', 3, 'Large outdoor table', TRUE),
(@restaurant_id, @section_bar, 'B1', 2, 1, 2, 'bar', 1, NULL, TRUE),
(@restaurant_id, @section_bar, 'B2', 2, 1, 2, 'bar', 2, 'High-top seating', TRUE);

-- Menu Categories
INSERT INTO menu_categories (restaurant_id, name, description, image_url, is_active, display_order) VALUES
(@restaurant_id, 'Appetizers', 'Start your meal with our delicious starters', 'https://images.unsplash.com/photo-1547592166-23ac45744acd', TRUE, 1),
(@restaurant_id, 'Salads', 'Fresh, crisp salads', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd', TRUE, 2),
(@restaurant_id, 'Pasta', 'Handmade pasta dishes', 'https://images.unsplash.com/photo-1555949258-eb67b1ef0ceb', TRUE, 3),
(@restaurant_id, 'Main Courses', 'Our signature entrees', 'https://images.unsplash.com/photo-1544025162-d76694265947', TRUE, 4),
(@restaurant_id, 'Desserts', 'Sweet endings to your meal', 'https://images.unsplash.com/photo-1551024601-bec78aea704b', TRUE, 5),
(@restaurant_id, 'Beverages', 'Wine, cocktails, and soft drinks', 'https://images.unsplash.com/photo-1470337458703-46ad1756a187', TRUE, 6);

SET @cat_apps = (SELECT id FROM menu_categories WHERE restaurant_id = @restaurant_id AND name = 'Appetizers' LIMIT 1);
SET @cat_salads = (SELECT id FROM menu_categories WHERE restaurant_id = @restaurant_id AND name = 'Salads' LIMIT 1);
SET @cat_pasta = (SELECT id FROM menu_categories WHERE restaurant_id = @restaurant_id AND name = 'Pasta' LIMIT 1);
SET @cat_mains = (SELECT id FROM menu_categories WHERE restaurant_id = @restaurant_id AND name = 'Main Courses' LIMIT 1);
SET @cat_desserts = (SELECT id FROM menu_categories WHERE restaurant_id = @restaurant_id AND name = 'Desserts' LIMIT 1);
SET @cat_bev = (SELECT id FROM menu_categories WHERE restaurant_id = @restaurant_id AND name = 'Beverages' LIMIT 1);

-- Menu Items (20 items)
INSERT INTO menu_items (
    restaurant_id, category_id, sku, name, description, price, image_url, prep_time_minutes, calories, spice_level,
    is_vegetarian, is_vegan, is_gluten_free, is_available, display_order
) VALUES
-- Appetizers
(@restaurant_id, @cat_apps, 'APP-001', 'Bruschetta', 'Toasted bread with fresh tomatoes, basil, and mozzarella', 8.99, 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f', 10, 260, 'none', TRUE, FALSE, FALSE, TRUE, 1),
(@restaurant_id, @cat_apps, 'APP-002', 'Calamari Fritti', 'Crispy fried squid rings with marinara sauce', 12.99, 'https://images.unsplash.com/photo-1625943555419-56a2cb596640', 14, 420, 'mild', FALSE, FALSE, FALSE, TRUE, 2),
(@restaurant_id, @cat_apps, 'APP-003', 'Antipasto Platter', 'Selection of Italian cured meats, cheeses, and olives', 16.99, NULL, 12, 510, 'none', FALSE, FALSE, TRUE, TRUE, 3),
(@restaurant_id, @cat_apps, 'APP-004', 'Mozzarella Sticks', 'Breaded mozzarella with marinara sauce', 9.99, NULL, 9, 390, 'none', TRUE, FALSE, FALSE, TRUE, 4),

-- Salads
(@restaurant_id, @cat_salads, 'SAL-001', 'Caesar Salad', 'Romaine lettuce, parmesan, croutons, caesar dressing', 10.99, NULL, 8, 330, 'none', TRUE, FALSE, FALSE, TRUE, 1),
(@restaurant_id, @cat_salads, 'SAL-002', 'Caprese Salad', 'Fresh mozzarella, tomatoes, basil, balsamic glaze', 11.99, NULL, 7, 290, 'none', TRUE, TRUE, TRUE, TRUE, 2),
(@restaurant_id, @cat_salads, 'SAL-003', 'Arugula Salad', 'Arugula, walnuts, gorgonzola, honey vinaigrette', 12.99, NULL, 8, 310, 'none', TRUE, FALSE, TRUE, TRUE, 3),

-- Pasta
(@restaurant_id, @cat_pasta, 'PAS-001', 'Spaghetti Carbonara', 'Classic Roman pasta with eggs, pancetta, and parmesan', 16.99, NULL, 18, 690, 'none', FALSE, FALSE, FALSE, TRUE, 1),
(@restaurant_id, @cat_pasta, 'PAS-002', 'Fettuccine Alfredo', 'Creamy alfredo sauce with parmesan', 15.99, NULL, 16, 740, 'none', TRUE, FALSE, FALSE, TRUE, 2),
(@restaurant_id, @cat_pasta, 'PAS-003', 'Penne Arrabbiata', 'Spicy tomato sauce with garlic and red pepper', 14.99, NULL, 15, 620, 'medium', TRUE, TRUE, FALSE, TRUE, 3),
(@restaurant_id, @cat_pasta, 'PAS-004', 'Lasagna', 'Layers of pasta, meat sauce, and cheese', 18.99, NULL, 20, 810, 'none', FALSE, FALSE, FALSE, TRUE, 4),
(@restaurant_id, @cat_pasta, 'PAS-005', 'Ravioli di Ricotta', 'Ricotta-filled ravioli with marinara sauce', 17.99, NULL, 17, 680, 'none', TRUE, FALSE, FALSE, TRUE, 5),

-- Main Courses
(@restaurant_id, @cat_mains, 'MAIN-001', 'Chicken Parmesan', 'Breaded chicken breast with marinara and mozzarella', 22.99, NULL, 22, 760, 'none', FALSE, FALSE, FALSE, TRUE, 1),
(@restaurant_id, @cat_mains, 'MAIN-002', 'Veal Marsala', 'Tender veal in marsala wine sauce with mushrooms', 26.99, NULL, 24, 720, 'none', FALSE, FALSE, TRUE, TRUE, 2),
(@restaurant_id, @cat_mains, 'MAIN-003', 'Salmon Piccata', 'Pan-seared salmon with lemon caper sauce', 24.99, NULL, 21, 590, 'none', FALSE, FALSE, TRUE, TRUE, 3),
(@restaurant_id, @cat_mains, 'MAIN-004', 'Osso Buco', 'Braised veal shank with risotto', 28.99, NULL, 28, 850, 'none', FALSE, FALSE, FALSE, TRUE, 4),

-- Desserts
(@restaurant_id, @cat_desserts, 'DES-001', 'Tiramisu', 'Classic Italian dessert with coffee and mascarpone', 8.99, NULL, 6, 430, 'none', TRUE, FALSE, FALSE, TRUE, 1),
(@restaurant_id, @cat_desserts, 'DES-002', 'Cannoli', 'Crispy shells filled with sweet ricotta', 7.99, NULL, 5, 390, 'none', TRUE, FALSE, FALSE, TRUE, 2),
(@restaurant_id, @cat_desserts, 'DES-003', 'Gelato', 'Three scoops of house-made gelato', 6.99, NULL, 4, 320, 'none', TRUE, FALSE, TRUE, TRUE, 3),

-- Beverages
(@restaurant_id, @cat_bev, 'BEV-001', 'House Wine', 'Glass of red or white wine', 8.99, NULL, 3, 120, 'none', TRUE, TRUE, TRUE, TRUE, 1),
(@restaurant_id, @cat_bev, 'BEV-002', 'Italian Soda', 'Sparkling water with flavored syrup', 4.99, NULL, 2, 90, 'none', TRUE, TRUE, TRUE, TRUE, 2);

-- Sample Reservations (past and future)
SET @customer_john = (SELECT id FROM customers WHERE email = 'john@example.com' LIMIT 1);
SET @customer_jane = (SELECT id FROM customers WHERE email = 'jane@example.com' LIMIT 1);
SET @table_1 = (SELECT id FROM tables WHERE restaurant_id = @restaurant_id AND table_number = 'T1' LIMIT 1);
SET @table_2 = (SELECT id FROM tables WHERE restaurant_id = @restaurant_id AND table_number = 'T2' LIMIT 1);
SET @table_3 = (SELECT id FROM tables WHERE restaurant_id = @restaurant_id AND table_number = 'T3' LIMIT 1);

-- Past reservations
INSERT INTO reservations (
    restaurant_id, customer_id, table_id, reservation_code, reservation_date, reservation_time, party_size,
    status, reservation_source, special_occasion, notes, seated_at, completed_at
) VALUES
(@restaurant_id, @customer_john, @table_1, 'RES-2024-001', DATE_SUB(CURDATE(), INTERVAL 7 DAY), '19:00:00', 2, 'completed', 'web', NULL, 'Anniversary dinner', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY) + INTERVAL 90 MINUTE),
(@restaurant_id, @customer_jane, @table_2, 'RES-2024-002', DATE_SUB(CURDATE(), INTERVAL 5 DAY), '20:00:00', 4, 'completed', 'phone', 'Birthday', NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY) + INTERVAL 95 MINUTE),
(@restaurant_id, @customer_john, @table_3, 'RES-2024-003', DATE_SUB(CURDATE(), INTERVAL 3 DAY), '18:30:00', 3, 'completed', 'web', NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY) + INTERVAL 88 MINUTE);

-- Future reservations
INSERT INTO reservations (
    restaurant_id, customer_id, table_id, reservation_code, reservation_date, reservation_time, party_size,
    status, reservation_source, special_occasion, notes
) VALUES
(@restaurant_id, @customer_john, @table_1, 'RES-2024-004', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '19:00:00', 2, 'confirmed', 'web', NULL, 'Window seat if possible'),
(@restaurant_id, @customer_jane, @table_2, 'RES-2024-005', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '20:00:00', 4, 'pending', 'admin', 'Birthday', 'Will bring cake'),
(@restaurant_id, NULL, @table_3, 'RES-2024-006', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '18:30:00', 3, 'confirmed', 'walk_in', NULL, NULL);

-- Guest details for guest reservation
SET @guest_reservation = (SELECT id FROM reservations WHERE reservation_code = 'RES-2024-006' LIMIT 1);
INSERT INTO reservation_guests (reservation_id, guest_name, guest_email, guest_phone, notes) VALUES
(@guest_reservation, 'Guest User', 'guest@example.com', '555-9999', 'Prefers outdoor seating');

-- Sample Cart + Preorder
SET @menu_item_1 = (SELECT id FROM menu_items WHERE restaurant_id = @restaurant_id AND sku = 'APP-001' LIMIT 1);
SET @menu_item_2 = (SELECT id FROM menu_items WHERE restaurant_id = @restaurant_id AND sku = 'PAS-003' LIMIT 1);
SET @reservation_for_cart = (SELECT id FROM reservations WHERE reservation_code = 'RES-2024-004' LIMIT 1);

INSERT INTO carts (customer_id, reservation_id, session_id, status, expires_at) VALUES
(@customer_john, @reservation_for_cart, NULL, 'attached', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(NULL, NULL, 'demo-session-guest-001', 'active', DATE_ADD(NOW(), INTERVAL 2 HOUR));

SET @cart_attached = (SELECT id FROM carts WHERE reservation_id = @reservation_for_cart LIMIT 1);
SET @cart_guest = (SELECT id FROM carts WHERE session_id = 'demo-session-guest-001' LIMIT 1);

INSERT INTO cart_items (cart_id, menu_item_id, quantity, price, notes) VALUES
(@cart_attached, @menu_item_1, 1, 8.99, 'No extra garlic'),
(@cart_attached, @menu_item_2, 2, 14.99, 'Extra chili'),
(@cart_guest, @menu_item_1, 1, 8.99, NULL);

-- Sample Audit Logs
INSERT INTO audit_logs (user_type, user_id, action, entity_type, entity_id, details, ip_address, user_agent) VALUES
('admin', 1, 'reservation_status_updated', 'reservation', @reservation_for_cart, 'Updated status to confirmed', '127.0.0.1', 'SeedScript/1.0'),
('customer', @customer_john, 'cart_item_added', 'cart', @cart_attached, 'Added APP-001 to cart', '127.0.0.1', 'SeedScript/1.0'),
('system', NULL, 'seed_initialized', 'database', 1, 'Seed data inserted successfully', '127.0.0.1', 'SeedScript/1.0');
