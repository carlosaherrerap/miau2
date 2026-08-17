
//EJEMPLO DE ROL
rol={
    "cod":"SAS",
    "nombre":"Supervisor de Almacén y Soporte Informático "
}

sede_reg={
    "nombre":"Ancash"
}

sede_juris={
    "id_sedereg":1,
    "nombre":"Callao"
}

//EJEMPLO DE USUARIO
usuario={
    "id_rol":1,
    "cod_usuario":"MI-1",
    "username":"carlos_herrera",
    "clave":"carlos_herrera",
    "nombres":"Carlos Alberto",
    "ape_pat":"Herrera",
    "ape_mat":"Palma",
    "id_sedereg":2,
    "id_sedejuris":1,
    "doc":75747335,
    "email":"carlos.a.h.palma@gmail.com",
    "estado":1
}

asignacion={
    "id_usuario":3,
    "id_sedereg":1 //asignado a rol o sede 
}

emisor={
    "id_usuario":4,
    "sede_reg":"Ancash",
    "sede_juris":"Chimbote"
}

receptor={
    "id_usuario":7,
    "estado": 1
}

ticket={
    "id_emisor":1,
    "id_receptor": 2,
    "fecha_emision": "2026-08-14 09:40:35"
}


categoria={
    "nombre":"Equipo Informático",
    "tipo":"Lector de código de barras"
}

ticket_detalle={
    "id_ticket":2, //2,3,4,5
    "id_categoria":3,  //3 al 11
    "descripcion_problema":"No se visualiza nada en el primer modulo",
    "fecha_recepcion":"2026-08-13 10:32:00",
    "nivel_importancia":"muy importante",
    "estado":"enviado",
    "fecha_estado_actual":"2026-08-14 10:32:00"
}

file={
    "id_ticket_detalle":2,
    "tipo":"imagen",
    "enlace":"https://pcseguro.es/wp-content/uploads/articles/askit/show-hidden-files-and-folders_es.jpg.webp"
}





