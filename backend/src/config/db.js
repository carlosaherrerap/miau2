import pg from 'pg';
import dotenv from 'dotenv';

dotenv.config();

const {Pool}=pg;

const pool=new Pool({
    host:process.env.HOST || 'localhost',
    user:process.env.POSTGRES_USER || 'miau_user_docker',
    database:process.env.POSTGRES_DB || 'miau_db_postgres',
    password:process.env.POSTGRES_PASSWORD || 'usuario_docker_permitido',
    port:process.env.POSTGRES_PORT || '5433'
});

export default pool;