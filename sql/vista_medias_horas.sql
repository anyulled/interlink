CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vista_medias_horas AS 
	select 
	c.ID AS canal_id, 
	c.NOMBRE AS canal, 
	ttv.id as tipo_tv_id,
	ttv.nombre as tipo_tv_nombre,
	ttv.orden_reporte as orden_tipo_tv,
	i.ID AS indicador_id,
	i.NOMBRE AS indicador,
	i.orden_reporte as orden_indicador,
	cat.id as categoria_id,
	cat.nombre as categoria,
	cat.orden_reporte as orden_categoria,
	imh.FECHA AS fecha,
	imh.HORA_INICIO AS hora_inicio,
	imh.HORA_FIN AS hora_fin,
	imh.PORCENTAJE_SHR AS porcentaje_shr,
	imh.AMR AS amr,
	imh.PORCENTAJE_AMR AS porcentaje_amr,
	imh.RCH AS rch, 
	imh.ATS as ats
	from
		indicador_medias_horas imh
		join canal c on c.ID = imh.CANAL_ID 
		join tipo_tv ttv on c.tipo_tv_id = ttv.id
		join indicador i on i.ID = imh.INDICADOR_ID
		join categoria cat on i.categoria_id = cat.id;
