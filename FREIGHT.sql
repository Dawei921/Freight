create database freight collate utf8_general_ci;

use freight;

/* 证件类型 */
create table credential_types
( aID int auto_increment primary key,
	typeName varchar(255) not null
);

insert into credential_types values(1,'身份证');
insert into credential_types values(2,'营业执照');

/*用户表*/
create table users
(
	aID int auto_increment primary key,
    credentialType int,
    credentialNumber varchar(255) unique not null,
    credentialPhotos varchar(255) not null comment '营业执照为一张照片，身份证依次是正面、反面、手持身份证照',
    fullName varchar(255) not null,
	mobileNumber varchar(255) not null,
    encryptPassword varchar(255) not null,
    vehicleCount int default 0,
    drivingLicense int,
    recordStatus int default 0 comment '默认0未审核，1审核通过，-1未通过审核',
    reason varchar(255) comment '未审核通过须填写此原因',
    createTime datetime default now(),
    updateTime datetime,
    foreign key(credentialType) references credential_types(aID)
);

/* 记录更新时间 */
delimiter //
create trigger users_update
before update
on users for each row
begin
	/* 如果已审核过(recordStatus<>0)不能再审核 -- 即取消审核恢复为默认状态0可以 */
	if(new.recordStatus<>0 and old.recordStatus<>0 and new.recordStatus<>old.recordStatus) then
		signal sqlstate '12345' set message_text='已审核过不能再审核';
    else
		set new.updateTime=now();
    end if;
end;
//
delimiter ;

/*  用户驾照信息 */
create table driving_licenses
(	aID int auto_increment primary key,
	userID int,
	drivingLicense varchar(255) not null,
    licensePhoto varchar(255) not null,    
    recordStatus int default 0 comment '-1撤消，默认0正常',
    createTime datetime default now(),
    updateTime dateTime,
    foreign key(userID) references users(aID)
);

/* 只要insert就检查是否已存在status为0驾照 */
delimiter //
create trigger driving_licenses_insert
before insert
on driving_licenses for each row
begin
	if(select count(aID) from driving_licenses where drivingLicense=new.drivingLicense and recordStatus=0)>0 then
		signal sqlstate '12345' set message_text='该驾驶证号码已存在，如果确认是您的驾驶证要更改请先撤消！';
    end if;
end;
//
/* 记录更新时间 */
create trigger driving_licenses_update
before update
on driving_licenses for each row
begin
	/* 如果已撤消过(recordStatus<>0)不能再撤消 -- 即取消撤消恢复为默认状态0可以 */
	if(new.recordStatus<>0 and old.recordStatus<>0 and new.recordStatus<>old.recordStatus) then
		signal sqlstate '12345' set message_text='已撤消过不能再撤消';
    else
		set new.updateTime=now();
    end if;
end;
//
delimiter ;

/*车型表*/
create table vehicle_types
(
	code char(3) primary key,
    describ varchar(255) not null
);
   
insert into vehicle_types values('001','平板');
insert into vehicle_types values('002','高栏');
insert into vehicle_types values('003','厢式');
insert into vehicle_types values('004','高低板');
insert into vehicle_types values('005','保温冷藏');
insert into vehicle_types values('006','危险品');
insert into vehicle_types values('007','自卸货车');

/*车长表*/
create table vehicle_longness
(
	aID int auto_increment primary key,
    unit varchar(255) not null,
    longness float
);
   
insert into vehicle_longness values(1,'米',4.2);     
insert into vehicle_longness values(2,'米',4.5);     
insert into vehicle_longness values(3,'米',5);     
insert into vehicle_longness values(4,'米',5.2);     
insert into vehicle_longness values(5,'米',6.2);     
insert into vehicle_longness values(6,'米',6.8);     
insert into vehicle_longness values(7,'米',7.2);     
insert into vehicle_longness values(8,'米',7.7);     
insert into vehicle_longness values(9,'米',7.8);     
insert into vehicle_longness values(10,'米',8.2);     
insert into vehicle_longness values(11,'米',8.6);     
insert into vehicle_longness values(12,'米',8.7);     
insert into vehicle_longness values(13,'米',9.6);     
insert into vehicle_longness values(14,'米',11.7);     
insert into vehicle_longness values(15,'米',12.5);     
insert into vehicle_longness values(16,'米',13);     
insert into vehicle_longness values(17,'米',13.5);     
insert into vehicle_longness values(18,'米',14);     
insert into vehicle_longness values(19,'米',16);     
insert into vehicle_longness values(20,'米',17);     
insert into vehicle_longness values(21,'米',17.5);     
insert into vehicle_longness values(22,'米',18); 

/*用户车辆信息*/
create table vehicles
(
	aID int auto_increment primary key,
    userID int,
    licensePlate varchar(255) not null,
    licensePlatePhoto varchar(255) not null,
    vehiclePhotos varchar(255) not null,
    recordStatus int default 0 comment '-1撤消，默认0正常',
    createTime datetime default now(),
    updateTime dateTime,
    foreign key(userID) references users(aID)
);

/* 只要insert就检查是否已存在status为0牌照 */
delimiter //
create trigger vehicles_insert
before insert
on vehicles for each row
begin
	if(select count(aID) from vehicles where licensePlate=new.licensePlate and recordStatus=0)>0 then
		signal sqlstate '12345' set message_text='该行驶证号码已存在，如果确认是您的行驶证要更改请先撤消！';
    end if;
end;
//
/* 记录更新时间 */
create trigger vehicles_update
before update
on vehicles for each row
begin
	/* 如果已撤消过(recordStatus<>0)不能再撤消 -- 即取消撤消恢复为默认状态0可以 */
	if(new.recordStatus<>0 and old.recordStatus<>0 and new.recordStatus<>old.recordStatus) then
		signal sqlstate '12345' set message_text='已撤消过不能再撤消';
    else
		set new.updateTime=now();
    end if;
end;
//
delimiter ;

/* 发货表 */
create table freights
(	aId int auto_increment primary key,
	vehicleLongness int,
    vehicleType char(3),
	beginPosition varchar(255) not null,
    beginProvince varchar(255) not null,
    beginCity varchar(255) not null,
    beginDistrict varchar(255) not null,
    beginStreet varchar(255),
	endPosition varchar(255) not null,
    endProvince varchar(255) not null,
    endCity varchar(255) not null,
    endDistrict varchar(255) not null,
    endStreet varchar(255),
	distance int comment '距离：公里（取整）',
    expenses double(15,2) comment '运费',
    covering varchar(255) comment '是否需要棉被',
    cubage int comment '体积（取整）',
    weight int comment '重量：千克（取整）',
    goodsType varchar(255) not null,
    screen varchar(255) not null,
    descib varchar(255),
    phone varchar(255) not null,
    publishTime datetime default now(),
    publishUser int,
    signOrder int,
    signFranchiser int,
    recordStatus int default 0 comment '默认0正常(可接单，其它状态不可再接单），1同意接单，2已接单，-1取消',
    updateTime datetime,
    reason varchar(255) comment '取消原因',
    closuretime datetime,
	foreign key(vehicleLongness) references vehicle_longness(aId),
	foreign key(vehicleType) references vehicle_types(code),
    foreign key(publishUser) references users(aID)
);

/* 记录更新时间 */
delimiter //
create trigger freights_update
before update
on freights for each row
begin
	/* 如果非关闭（完成或取消）则记录更新时间 */
	if new.recordStatus<>2 or new.recordStatus<>-1 then
		set new.updateTime=now();
    end if;
end;
//
delimiter ;
  
/* 驾驶员表 */
create table drivers
(	aID int auto_increment primary key,
	drivingLicense varchar(255),
    driver varchar(255),
    inputUser int,
    foreign key(inputUser) references users(aID)
);    
  
 /* 接单意愿反馈表 */
create table freight_franchisers
(	aID int auto_increment primary key,
	freightID int,
    franchiserUser int,
    franchiser int comment '1我想接单-1运费太低',
    vehicle int,
    drivers varchar(255) comment 'driver表aID用,号分隔',
    franchiserTime datetime default now(),
    replyUser int,
    reply int comment '1同意接单',
    replyTime datetime,
	foreign key(freightID) references freights(aID),
	foreign key(vehicle) references vehicles(aID),
    foreign key(franchiserUser) references users(aID),
    foreign key(replyUser) references users(aID)
);  
  
  
 /********视图*******/ 
  
/* 用户视图 */
create view v_users as
	select a.aID as userID,
		a.fullName as fullName,
        b.typeName as credentialType,
		a.credentialNumber as credentialNumber,
        a.credentialPhotos as credentialPhotos,
        a.mobileNumber as mobileNumber,
        a.drivingLicense as drivingLicenseID,
        c.drivingLicense as drivingLicense,
        a.recordStatus as recordStatus
	from users as a
    left join credential_types as b on b.aID=a.credentialType
    left join driving_licenses as c on c.aID=a.drivingLicense;

/* 货源视图 */
create view v_freights as
	select a.aId as freightID,
       b.longness as vehicleLongness,
        b.unit as longnessUnit,
        c.describ as vehicleType,
		a.beginPosition as beginPosition,        
        a.beginProvince as beginProvince,
        a.beginCity as beginCity,
        a.beginDistrict as beginDistrict,
		a.endPosition as endPosition,
		a.endProvince as endProvince,
		a.endCity as endCity,
		a.endDistrict as endDistrict,
		a.distance as distance,
		a.expenses as expenses,
		a.cubage as cubage,		
        a.weight as weight,
		a.goodsType as goodsType,
		a.recordStatus as recordStatus,
		a.publishTime as publishTime,
        a.publishUser as publishUser,
        e.licensePlate as vehicle,
        f.fullName as franchiserUser,
        f.mobileNumber as mobileNumber
    from freights as a
    left join vehicle_longness as b on b.aID=a.vehicleLongness
    left join vehicle_types as c on c.code=a.vehicleType
    left join freight_franchisers as d on d.aID=a.signFranchiser
    left join vehicles as e on e.aID=d.vehicle
    left join users as f on f.aID=d.franchiserUser;
     
/* 接单意愿反馈视图（为性能优化仅未完成订单货源反馈） */
create view v_freight_franchisers as
	select a.aID as franchiserID,
		a.freightID as freightID,
        (case a.franchiser when 1 then '我想接单' when -1 then '运费太低 ' end) as franchiser,
		a.franchiserTime as franchiserTime,
		c.fullName as franchiserUser        
    from freight_franchisers as a
    left join freights as b on b.aId=a.freightID
    left join users as c on c.aID=a.franchiserUser
    where b.recordStatus>-1
    and b.recordStatus<2;
       
/* 我想接单视图（为性能优化仅未完成订单货源反馈） */
create view v_hope_freights as
	select a.aID as franchiserID,
		a.franchiserUser as franchiserUser,
		a.freightID as freightID,
        b.beginProvince as beginProvince,
        b.beginCity as beginCity,
		b.endProvince as endProvince,
		b.endCity as endCity,
		b.publishTime as publishTime
    from freight_franchisers as a
    left join freights as b on b.aID=a.freightID
    where b.recordStatus>-1
    and b.recordStatus<2; 
    
    
    
    
/*******************/
/* 订单 */
/*create table orders
(	aID int auto_increment primary key,
	freightID int,
    franchiserID int,
    brokerage float(7,2),
    orderTime datetime default now(),
    orderUser int,
    orderStatus int default 0 comment '默认0进行中，1完成，-1取消',
    reason varchar(255) comment '取消原因',
    logTime datetime,
    foreign key(freightID) references freights(aID),
    foreign key(franchiserID) references freight_franchisers(aID),
    foreign key(orderUser) references users(aID)
);

/* 记录更新时间 */
/*delimiter //
create trigger orders_update
before update
on orders for each row
		set new.logTime=now();
//
delimiter ;
    
/* 订单货源视图 */
/*create view v_order_freight as
	select a.aID as orderID,
		a.orderUser as orderUser,
		a.orderTime as orderTime,
        a.orderStatus as orderStatus,
		a.freightID as freightID,        
        b.beginProvince as beginProvince,
        b.beginCity as beginCity,
		b.endProvince as endProvince,
		b.endCity as endCity
    from orders as a
    left join freights as b on b.aID=a.freightID;
    
/* freights、freight_franchisers、orders三表视图 */
/*create view v_freight_franchiser_order as
	select a.aId as freightID,
        b.longness as vehicleLongness,
        b.unit as longnessUnit,
        c.describ as vehicleType,
		a.beginPosition as beginPosition,        
        a.beginProvince as beginProvince,
        a.beginCity as beginCity,
        a.beginDistrict as beginDistrict,
		a.endPosition as endPosition,
		a.endProvince as endProvince,
		a.endCity as endCity,
		a.endDistrict as endDistrict,
		a.distance as distance,
		a.expenses as expenses,
		a.cubage as cubage,		
        a.weight as weight,
		a.goodsType as goodsType,
		a.recordStatus as recordStatus,
		a.publishTime as publishTime,
		a.publishUser as publishUser,
        d.franchiserUser as franchiserUser,
        e.fullName as fullName,
        e.mobileNumber as mobileNumber
    from freights as a
    left join vehicle_longness as b on b.aID=a.vehicleLongness
    left join vehicle_types as c on c.code=a.vehicleType
    left join freight_franchisers as d on d.aID=a.signFranchiser
    left join users as e on e.aID=d.franchiserUser
    where d.reply=1;*/
 