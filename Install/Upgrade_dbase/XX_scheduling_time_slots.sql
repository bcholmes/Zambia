create table room_availability_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) NOT NULL
);

create table room_availability_slot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    availability_schedule_id INT NOT NULL,
    start_time time NOT NULL,
    end_time time NOT NULL,
    divisionid INT
);

insert into room_availability_schedule (name) values ('Standard slots');

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '08:30:00', '09:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '10:00:00', '11:15:00', 2 from room_availability_schedule;

-- Lunch

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '13:00:00', '14:15:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '14:30:00', '15:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '16:00:00', '17:15:00', 2 from room_availability_schedule;

-- Dinner

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '19:30:00', '20:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '21:00:00', '22:15:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '22:30:00', '23:45:00', 2 from room_availability_schedule;

create table room_to_availability (
    roomid INT NOT NULL,
    availability_id INT NOT NULL,
    day INT NOT NULL
);

insert into room_availability_schedule (name) values ('Friday event slots');

-- Opening Ceremonies
insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '13:00:00', '15:45:00', 3 from room_availability_schedule;

-- Vid Party
insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '20:00:00', '27:00:00', 3 from room_availability_schedule;



insert into room_availability_schedule (name) values ('Saturday event slots');

-- Otherwise Auction
insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '19:30:00', '21:30:00', 3 from room_availability_schedule;



insert into room_availability_schedule (name) values ('Standard morning/afternoon slots');

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '08:30:00', '09:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '10:00:00', '11:15:00', 2 from room_availability_schedule;

-- Lunch

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '13:00:00', '14:15:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '14:30:00', '15:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '16:00:00', '17:15:00', 2 from room_availability_schedule;


insert into room_availability_schedule (name) values ('Standard afternoon/evening slots');

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '13:00:00', '14:15:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '14:30:00', '15:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '16:00:00', '17:15:00', 2 from room_availability_schedule;

-- Dinner

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '19:30:00', '20:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '21:00:00', '22:15:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '22:30:00', '23:45:00', 2 from room_availability_schedule;


insert into room_availability_schedule (name) values ('Standard morning-only slots');

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '08:30:00', '09:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '10:00:00', '11:15:00', 2 from room_availability_schedule;



insert into room_availability_schedule (name) values ('Standard morning/afternoon with Parties');

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '08:30:00', '09:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '10:00:00', '11:15:00', 2 from room_availability_schedule;

-- Lunch

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '13:00:00', '14:15:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '14:30:00', '15:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '16:00:00', '17:15:00', 2 from room_availability_schedule;

-- Dinner

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '21:00:00', '27:00:00', 11 from room_availability_schedule;


insert into room_availability_schedule (name) values ('Standard afternoon with Parties');

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '13:00:00', '14:15:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '14:30:00', '15:45:00', 2 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '16:00:00', '17:15:00', 2 from room_availability_schedule;

-- Dinner

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '21:00:00', '27:00:00', 11 from room_availability_schedule;




insert into room_availability_schedule (name) values ('Readings morning/afternoon with Parties');

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '08:30:00', '09:45:00', 100 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '10:00:00', '11:15:00', 100 from room_availability_schedule;

-- Lunch

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '13:00:00', '14:15:00', 100 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '14:30:00', '15:45:00', 100 from room_availability_schedule;

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '16:00:00', '17:15:00', 100 from room_availability_schedule;

-- Dinner

insert into room_availability_slot (availability_schedule_id, start_time, end_time, divisionid)
select max(id), '21:00:00', '27:00:00', 11 from room_availability_schedule;



insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Assembly'
and a.name = 'Standard afternoon/evening slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Assembly'
and a.name = 'Standard slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Assembly'
and a.name = 'Standard slots';


insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Caucus'
and a.name = 'Standard afternoon/evening slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Caucus'
and a.name = 'Standard slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Caucus'
and a.name = 'Standard slots';


insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Wisconsin'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Capital A'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Capital B'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Wisconsin'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Capital A'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Capital B'
and a.name = 'Standard morning/afternoon slots';


-- virtual rooms

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 1'
and a.name = 'Standard afternoon/evening slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 1'
and a.name = 'Standard slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 1'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 1'
and a.name = 'Standard morning-only slots';



insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 2'
and a.name = 'Standard afternoon/evening slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 2'
and a.name = 'Standard slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 2'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'Ansible 2'
and a.name = 'Standard morning-only slots';



insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 2'
and a.name = 'Readings morning/afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 2'
and a.name = 'Readings morning/afternoon with Parties';


insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 3'
and a.name = 'Standard afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 3'
and a.name = 'Standard morning/afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 3'
and a.name = 'Standard morning/afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 3'
and a.name = 'Standard morning-only slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 4'
and a.name = 'Standard afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 4'
and a.name = 'Standard morning/afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 4'
and a.name = 'Standard morning/afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 4'
and a.name = 'Standard morning-only slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 5'
and a.name = 'Standard afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 5'
and a.name = 'Standard morning/afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 5'
and a.name = 'Standard morning/afternoon with Parties';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'Conference 5'
and a.name = 'Standard morning-only slots';


insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'University B'
and a.name = 'Standard afternoon/evening slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'University B'
and a.name = 'Standard slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 3
from Rooms r, room_availability_schedule a
where r.roomname = 'University B'
and a.name = 'Standard morning/afternoon slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'University B'
and a.name = 'Standard morning-only slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'Assembly'
and a.name = 'Standard morning-only slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 4
from Rooms r, room_availability_schedule a
where r.roomname = 'Caucus'
and a.name = 'Standard morning-only slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 1
from Rooms r, room_availability_schedule a
where r.roomname = 'Capital/Wisconsin'
and a.name = 'Friday event slots';

insert into room_to_availability
(roomid, availability_id, day)
select r.roomid, a.id, 2
from Rooms r, room_availability_schedule a
where r.roomname = 'Capital/Wisconsin'
and a.name = 'Saturday event slots';

alter table Rooms add column is_online char(1) NOT NULL DEFAULT 'N';


