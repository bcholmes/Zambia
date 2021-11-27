alter table `Divisions` add column email_address varchar(255);
alter table `Divisions` add column brainstorm_support char(1) default 'N';

alter table `Tracks` add column divisionid int;
alter table `Tracks` add column `description` text;
alter table `Tracks` add foreign key (divisionid) references `Divisions`(divisionid) on delete SET NULL on update cascade;