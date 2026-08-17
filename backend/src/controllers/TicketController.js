import pool from './../config/db.js'

const allTickets=async(req,res)=>{
    try{
        const resultado=await pool.query("SELECT NOW()");
        res.json({"Resultado a las: ":resultado});
    }catch(err){
        res.status(500).json({"Hubo un error en la consulta":err});
    }
}

export default allTickets;

