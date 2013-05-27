/*CONSUMO TELEVISIVO POR TARGETS SOCIO-DEMOGRAFICO*/
SELECT `tipo_tv`, `canal`, `indicador`, categoria, `amr` miles, `porcentaje_amr`, `porcentaje_shr` FROM `vista_totales`
where categoria IN ('CLASE SOCIAL','EDAD','SEXO');

/*CONSUMO TELEVISIVO POR AMBITOS */
SELECT `tipo_tv`, `canal`, `indicador`, `amr` miles, `porcentaje_amr`, `porcentaje_shr` FROM `vista_totales`
where categoria = 'REGION';

/*PROGRAMACION POR CANALES */
SELECT @rownum := @rownum +1 AS rownum, aux. * 
FROM (

SELECT canal, programa, genero, fecha, hora_inicio, amr miles, porcentaje_amr, porcentaje_shr
FROM vista_emisiones
ORDER BY amr DESC , porcentaje_amr DESC , porcentaje_shr DESC
)aux, (

SELECT @rownum :=0)r

/*REPARTO DE AUDIENCIAS POR CANAL*/
select 
	vt.canal,
	vt.tipo_tv,
	vt.fecha,
	case 
		when DATE_FORMAT(vt.fecha, '%W')= 'Monday' then 'Lunes' 
		when DATE_FORMAT(vt.fecha, '%W')= 'Tuesday' then 'Martes' 
		when DATE_FORMAT(vt.fecha, '%W')= 'Wednesday' then 'Miercoles'
		when DATE_FORMAT(vt.fecha,'%W')= 'Thursday' then 'Jueves'
		when DATE_FORMAT(vt.fecha, '%W')= 'Friday'  then 'Viernes'
		when DATE_FORMAT(vt.fecha, '%W')= 'Saturday'  then 'Sabado'
		when DATE_FORMAT(vt.fecha, '%W')= 'Sunday'  then 'Domingo'	  		
                else DATE_FORMAT(vt.fecha, '%W') 
	end  dia_semana,
	vt.porcentaje_shr, 
	vt.amr, 
	vt.porcentaje_amr, 
	vt.ats
from vista_totales vt
