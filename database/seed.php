<?php


$db = new PDO('sqlite:database/database.sqlite');


$db->exec("
    INSERT INTO users (name, email, password_hash, role) VALUES
    ('Admin User', 'admin@example.com', '" . password_hash('adminpass', PASSWORD_DEFAULT) . "', 'admin'),
    ('Agent User', 'agent@example.com', '" . password_hash('agentpass', PASSWORD_DEFAULT) . "', 'agent');
");


$db->exec("
    INSERT INTO departments (name) VALUES
    ('Support'),
    ('Sales');
");


$db->exec("
    INSERT INTO tickets (title, description, status, user_id, department_id) VALUES
    ('Cannot login', 'User cannot login to the system', 'open', 1, 1),
    ('Payment issue', 'Customer reports payment not processed', 'open', 2, 2);
");


$db->exec("
    INSERT INTO ticket_notes (user_id, ticket_id, note) VALUES
    (1, 1, 'Checked user credentials, looks fine.'),
    (2, 2, 'Asked customer for more details.');
");

echo "Seed data inserted successfully.\n";
