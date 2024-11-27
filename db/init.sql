CREATE TABLE users (
                       id SERIAL PRIMARY KEY,
                       username VARCHAR(255) UNIQUE NOT NULL,
                       password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE conversion_history (
                                    id SERIAL PRIMARY KEY,
                                    user_id INT NOT NULL,
                                    from_unit VARCHAR(50),
                                    to_unit VARCHAR(50),
                                    input_value DECIMAL(10, 2),
                                    result_value DECIMAL(10, 2),
                                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    FOREIGN KEY (user_id) REFERENCES users (id)
);