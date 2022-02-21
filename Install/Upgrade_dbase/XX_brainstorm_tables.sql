create table con_key_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    con_id int not null,
    external_key varchar(255) not null,
    from_time TIMESTAMP NOT NULL DEFAULT NOW(),
    to_time TIMESTAMP NOT NULL DEFAULT NOW(),

    CONSTRAINT external_key_uniq UNIQUE (con_id, external_key)
);

create view current_con as
    select c.id, c.name, p.name as perennial_name, c.con_start_date, c.con_end_date
      from reg_con_info c, reg_perennial_con_info p
    where c.perennial_con_id = p.id
    and c.active_to_time > now()
    and c.active_from_time <= now();

insert into con_key_dates (con_id, external_key, to_time) 
select id, 'PANEL_BRAINSTORM', '2022-01-08 05:59:59'
from current_con;


insert into con_key_dates (con_id, external_key, to_time) 
select id, 'ACADEMIC_BRAINSTORM', '2022-03-01 05:59:59'
from current_con;

alter table `Divisions` add column external_key varchar(255);

insert into con_key_dates (con_id, external_key, to_time) 
select id, 'GAMING_BRAINSTORM', '2022-03-01 05:59:59'
from current_con;

insert into con_key_dates (con_id, external_key, to_time) 
select id, 'READINGS_BRAINSTORM', '2022-03-01 05:59:59'
from current_con;

