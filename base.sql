create table category
(
	id int auto_increment
		primary key,
	parent_id int null,
	name varchar(150) null,
	content longtext null,
	type enum('node', 'leaf') not null,
	`order` tinyint(3) unsigned default '0' not null,
	constraint category_category_id_fk
		foreign key (parent_id) references category (id)
			on update cascade on delete set null
)
;

create index category_category_id_fk
	on category (parent_id)
;

