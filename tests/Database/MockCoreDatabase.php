<?php


namespace Tests\Database;


use Illuminate\Support\Facades\DB;

class MockCoreDatabase
{
    public static function create()
    {
        DB::connection('mysql_core')->statement(
            "create table if not exists mship_account
            (
                id int unsigned not null
                    primary key,
                slack_id varchar(10) null,
                name_first varchar(50) not null,
                name_last varchar(50) not null,
                nickname varchar(60) null,
                email varchar(200) null,
                password varchar(60) null,
                password_set_at timestamp null,
                password_expires_at timestamp null,
                last_login timestamp null,
                last_login_ip varchar(45) default '0.0.0.0' not null,
                remember_token varchar(100) null,
                gender varchar(1) null,
                experience varchar(1) null,
                age smallint(5) unsigned default 0 not null,
                inactive tinyint(1) default 0 not null,
                is_invisible tinyint(1) default 0 not null,
                debug tinyint(1) default 0 not null,
                joined_at timestamp null,
                created_at timestamp null,
                updated_at timestamp null,
                cert_checked_at timestamp null,
                deleted_at timestamp null,
                constraint mship_account_slack_id_unique
                    unique (slack_id)
            );"
        );
    }

    public static function destroy()
    {
        DB::connection('mysql_core')->statement(
            "drop table if exists mship_account"
        );
    }
}
