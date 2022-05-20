create table mobile_device (
    id INT AUTO_INCREMENT PRIMARY KEY,
    badgeid varchar(15) not null,
    os varchar(15) not null,
    client_id varchar(64) not null,
    device_token varchar(255) not null,
    device_model varchar(255) not null,

    FOREIGN KEY (`badgeid`)
        REFERENCES `Participants`(`badgeid`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;