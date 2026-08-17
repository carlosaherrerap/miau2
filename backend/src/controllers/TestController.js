import pool from '../config/db.js'

const getTickets=async(req,res)=>{
    try{
        const resp=await pool.query("SELECT * FROM usuario");
        console.log(resp.rows)
    }catch(error){
        console.error("Hubo un error en tu consulta TESTCONTROLLER",error);
    }
}

export default getTickets;