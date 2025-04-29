-- Table: buses
CREATE TABLE IF NOT EXISTS "buses" (
    "id" integer primary key autoincrement not null,
    "bus_number" varchar not null,
    "capacity" integer,
    "model" varchar,
    "manufacturer" varchar,
    "year_of_manufacture" integer,
    "license_plate" varchar,
    "registration_expiry" date,
    "last_maintenance_date" date,
    "next_maintenance_date" date,
    "status" varchar check (
        "status" in ('active', 'maintenance', 'out_of_service')
    ) not null default 'active',
    "fuel_type" varchar,
    "fuel_efficiency" numeric,
    "features" text,
    "notes" text,
    "image_path" varchar,
    "user_id" integer not null,
    "driver_id" integer not null,
    "created_at" datetime,
    "updated_at" datetime,
    foreign key ("user_id") references "users" ("id") on delete cascade,
    foreign key ("driver_id") references "drivers" ("id") on delete cascade
);

-- Table: destinations
CREATE TABLE IF NOT EXISTS "destinations" (
    "id" integer primary key autoincrement not null,
    "name" varchar not null,
    "address" varchar,
    "city" varchar,
    "state" varchar,
    "country" varchar,
    "postal_code" varchar,
    "latitude" numeric,
    "longitude" numeric,
    "description" text,
    "facilities" text,
    "contact_phone" varchar,
    "contact_email" varchar,
    "opening_hours" varchar,
    "notes" text,
    "image_path" varchar,
    "domain_id" integer not null,
    "created_at" datetime,
    "updated_at" datetime,
    foreign key ("domain_id") references "domains" ("id") on delete cascade
);

-- Table: domains
CREATE TABLE IF NOT EXISTS "domains" (
    "id" integer primary key autoincrement not null,
    "name" varchar not null,
    "description" text,
    "region" varchar,
    "country" varchar,
    "contact_person" varchar,
    "contact_email" varchar,
    "contact_phone" varchar,
    "is_active" tinyint (1) not null default '1',
    "destination_count" integer not null default '0',
    "color_code" varchar,
    "icon" varchar,
    "image_path" varchar,
    "notes" text,
    "created_at" datetime,
    "updated_at" datetime
);

-- Table: drivers
CREATE TABLE IF NOT EXISTS "drivers" (
    "id" integer primary key autoincrement not null,
    "name" varchar not null,
    "license_number" varchar not null,
    "phone" varchar,
    "email" varchar,
    "address" varchar,
    "date_of_birth" date,
    "license_expiry_date" date,
    "status" varchar check (
        "status" in ('active', 'inactive', 'on_leave', 'terminated')
    ) not null default 'active',
    "years_of_experience" integer,
    "notes" text,
    "emergency_contact_name" varchar,
    "emergency_contact_phone" varchar,
    "image_path" varchar,
    "user_id" integer not null,
    "created_at" datetime,
    "updated_at" datetime,
    foreign key ("user_id") references "users" ("id") on delete cascade
);

-- Table: paths
CREATE TABLE IF NOT EXISTS "paths" (
    "id" integer primary key autoincrement not null,
    "name" varchar,
    "start_destination_id" integer not null,
    "end_destination_id" integer not null,
    "total_distance" numeric,
    "total_duration" integer,
    "number_of_stops" integer not null default '0',
    "route_description" text,
    "route_map_url" varchar,
    "directions_json" text,
    "path_code" varchar,
    "is_circular" tinyint (1) not null default '0',
    "notes" text,
    "created_at" datetime,
    "updated_at" datetime,
    foreign key ("start_destination_id") references "destinations" ("id") on delete cascade,
    foreign key ("end_destination_id") references "destinations" ("id") on delete cascade
);

-- Table: trips
CREATE TABLE IF NOT EXISTS "trips" (
    "id" integer primary key autoincrement not null,
    "bus_id" integer not null,
    "path_id" integer not null,
    "departure_time" datetime not null,
    "arrival_time" datetime not null,
    "price" numeric not null,
    "trip_code" varchar,
    "available_seats" integer,
    "booked_seats" integer not null default '0',
    "status" varchar check (
        "status" in (
            'scheduled',
            'in_progress',
            'completed',
            'cancelled',
            'delayed'
        )
    ) not null default 'scheduled',
    "delay_reason" text,
    "cancellation_reason" text,
    "distance" numeric,
    "estimated_duration" integer,
    "fuel_consumption" numeric,
    "notes" text,
    "created_at" datetime,
    "updated_at" datetime,
    foreign key ("bus_id") references "buses" ("id") on delete cascade,
    foreign key ("path_id") references "paths" ("id") on delete cascade
);

-- Table: users
CREATE TABLE IF NOT EXISTS "users" (
    "id" integer primary key autoincrement not null,
    "name" varchar not null,
    "email" varchar not null,
    "email_verified_at" datetime,
    "password" varchar not null,
    "company_address" varchar,
    "phone" varchar,
    "remember_token" varchar,
    "created_at" datetime,
    "updated_at" datetime
);

-- Index: buses_bus_number_unique
CREATE UNIQUE INDEX IF NOT EXISTS "buses_bus_number_unique" on "buses" ("bus_number");

-- Index: drivers_license_number_unique
CREATE UNIQUE INDEX IF NOT EXISTS "drivers_license_number_unique" on "drivers" ("license_number");

-- Index: paths_path_code_unique
CREATE UNIQUE INDEX IF NOT EXISTS "paths_path_code_unique" on "paths" ("path_code");

-- Index: trips_trip_code_unique
CREATE UNIQUE INDEX IF NOT EXISTS "trips_trip_code_unique" on "trips" ("trip_code");

-- Index: users_email_unique
CREATE UNIQUE INDEX IF NOT EXISTS "users_email_unique" on "users" ("email");
