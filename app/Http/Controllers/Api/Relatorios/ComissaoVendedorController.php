<?php

namespace App\Http\Controllers\Api\Relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ComissaoVendedorController extends Controller
{
    use ApiResponser;

    public function relatorioComissaoVendedor(Request $request): JsonResponse
    {
        try {
            $sql = "SELECT tot.matricula, tot.codvend, tot.nome, tot.codsituacao, tot.filial, sum(tot.vendasd) as vendasd, sum(tot.vendacdsc) as vendacdsc,
                            sum(tot.vendacdcc) as vendacdcc, sum(tot.devsd) as devsd, sum(tot.devcdsc) as devcdsc, sum(tot.devcdcc) as devcdcc, sum(tot.comissao1) as comissao1,
                            sum(tot.comissao05) as comissao05, tot.percentadiant, tot.codfuncao
                        from (
                        select a.matricula, a.CODVEND, a.nome, a.CODSITUACAO, a.filial, sum(a.vendasd) as vendasd, sum(a.vendacdsc) as vendacdsc,
                            sum(a.vendacdcc) as vendacdcc, sum(a.devsd) as devsd, sum(a.devcdsc) as devcdsc, sum(a.devcdcc) as devcdcc, sum(a.comissao1) as comissao1,
                            sum(a.comissao05) as comissao05, a.PERCENTADIANT, a.CODFUNCAO
                            from (
                                select v.matricula,vcp.CODVEND, v.nome, pf.CODSITUACAO, v.filial, 
                                    case when c.motivo is null then sum(vcp.preco) end as vendasd,
                                    case when c.motivo IN (4,5) then sum(vcp.preco) end as vendacdsc,
                                    case when c.motivo NOT IN (4,5) then sum(vcp.preco) end as vendacdcc,
                                    0 as devsd,
                                    0 as devcdsc,
                                    0 as devcdcc,
                                    sum(vcp.preco) * 0.01 as comissao1,
                                    case when c.motivo NOT IN (4,5) OR c.MOTIVO is null then sum(vcp.preco) * 0.005 END as comissao05,
                                    pf.PERCENTADIANT, pf.CODFUNCAO
                                    from BDAPOIO..DASH_VENDA_COM_PEDIDO vcp
                                    inner join VENDEDOCAD v on v.codvend = vcp.CODVEND collate SQL_Latin1_General_CP1_CI_AI
                                    inner join (SELECT CHAPA, CODSITUACAO,PERCENTADIANT,CODFUNCAO FROM TOTVS_RM.DBO.PFUNC_job PF WHERE CHAPA <> '000000' 
                                        AND CODSITUACAO IN('R','A','F', 'P', 'T','Y')) PF ON PF.CHAPA = REPLICATE('0', 6 - LEN(v.MATRICULA)) + RTRIM(v.MATRICULA)
                                    LEFT JOIN CONTROLEDESCONTO c on c.NUMPED = vcp.numped AND c.CODPRO = vcp.codpro collate SQL_Latin1_General_CP1_CI_AI
                                    LEFT JOIN MOTIVODESCONTO m on m.CODIGO = c.MOTIVO
                                    where vcp.DATA_VENDA BETWEEN ? and ?
                                    group by v.matricula,vcp.CODVEND, v.nome, pf.CODSITUACAO, v.filial, c.motivo, pf.PERCENTADIANT, pf.CODFUNCAO )a
                        group by a.matricula, a.CODVEND, a.nome, a.CODSITUACAO, a.filial, a.PERCENTADIANT, a.CODFUNCAO
                    
                            UNION
                    
                        select a.matricula, a.CODVEND, a.nome, a.CODSITUACAO, a.filial, sum(a.vendasd) as vendasd, sum(a.vendacdsc) as vendacdsc,
                            sum(a.vendacdcc) as vendacdcc, sum(a.devsd) as devsd, sum(a.devcdsc) as devcdsc, sum(a.devcdcc) as devcdcc, sum(a.comissao1) as comissao1,
                            sum(a.comissao05) as comissao05, a.PERCENTADIANT, a.CODFUNCAO
                            from (
                                select v.matricula,vsp.CODVEND, v.nome, pf.CODSITUACAO, v.filial, 
                                    case when c.motivo is null then sum(vsp.preco) end as vendasd,
                                    case when c.motivo IN (4,5) then sum(vsp.preco) end as vendacdsc,
                                    case when c.motivo NOT IN (4,5) then sum(vsp.preco) end as vendacdcc,
                                    0 as devsd,
                                    0 as devcdsc,
                                    0 as devcdcc,
                                    sum(vsp.preco) * 0.01 as comissao1,
                                    case when c.motivo NOT IN (4,5) OR c.MOTIVO is null then sum(vsp.preco) * 0.005 END as comissao05,
                                    pf.PERCENTADIANT, pf.CODFUNCAO
                                    from BDAPOIO..DASH_VENDA_SEM_PEDIDO vsp
                                    inner join VENDEDOCAD v on v.codvend = vsp.CODVEND collate SQL_Latin1_General_CP1_CI_AI
                                    inner join (SELECT CHAPA, CODSITUACAO,PERCENTADIANT,CODFUNCAO FROM TOTVS_RM.DBO.PFUNC_job PF WHERE CHAPA <> '000000' 
                                        AND CODSITUACAO IN('R','A','F', 'P', 'T','Y')) PF ON PF.CHAPA = REPLICATE('0', 6 - LEN(v.MATRICULA)) + RTRIM(v.MATRICULA)
                                    LEFT JOIN CONTROLEDESCONTO c on c.numord = vsp.numord AND c.CODPRO = vsp.codpro collate SQL_Latin1_General_CP1_CI_AI
                                    LEFT JOIN MOTIVODESCONTO m on m.CODIGO = c.MOTIVO
                                    where vsp.DATA_VENDA BETWEEN ? and ?
                                    group by v.matricula,vsp.CODVEND, v.nome, pf.CODSITUACAO, v.filial, c.motivo, pf.PERCENTADIANT, pf.CODFUNCAO )a
                        group by a.matricula, a.CODVEND, a.nome, a.CODSITUACAO, a.filial, a.PERCENTADIANT, a.CODFUNCAO
                    
                            UNION
                    
                        select a.matricula, a.CODVEND, a.nome, a.CODSITUACAO, a.filial, sum(a.vendasd) as vendasd, sum(a.vendacdsc) as vendacdsc,
                            sum(a.vendacdcc) as vendacdcc, sum(a.devsd) as devsd, sum(a.devcdsc) as devcdsc, sum(a.devcdcc) as devcdcc, sum(a.comissao1) as comissao1,
                            sum(a.comissao05) as comissao05, a.PERCENTADIANT, a.CODFUNCAO
                            from (
                                select v.matricula,dev.CODVEND, v.nome, pf.CODSITUACAO, v.filial, 
                                    0 as vendasd,
                                    0 as vendacdsc,
                                    0 as vendacdcc,
                                    case when c.motivo is null then sum(dev.preco) end as devsd,
                                    case when c.motivo IN (4,5) then sum(dev.preco) end as devcdsc,
                                    case when c.motivo NOT IN (4,5) then sum(dev.preco) end as devcdcc,
                                    sum(dev.preco) * -0.01 as comissao1,
                                    case when c.motivo NOT IN (4,5) OR c.MOTIVO is null then sum(dev.preco) * -0.005 END as comissao05,
                                    pf.PERCENTADIANT, pf.CODFUNCAO
                                    from BDAPOIO..DASH_DEV_1 dev
                                    inner join VENDEDOCAD v on v.codvend = dev.CODVEND collate SQL_Latin1_General_CP1_CI_AI
                                    inner join (SELECT CHAPA, CODSITUACAO,PERCENTADIANT,CODFUNCAO FROM TOTVS_RM.DBO.PFUNC_job PF WHERE CHAPA <> '000000' 
                                        AND CODSITUACAO IN('R','A','F', 'P', 'T','Y')) PF ON PF.CHAPA = REPLICATE('0', 6 - LEN(v.MATRICULA)) + RTRIM(v.MATRICULA)
                                    LEFT JOIN CONTROLEDESCONTO c on c.numord = dev.numord AND c.CODPRO = dev.codpro collate SQL_Latin1_General_CP1_CI_AI
                                    LEFT JOIN MOTIVODESCONTO m on m.CODIGO = c.MOTIVO
                                    where dev.DATA_DEV BETWEEN ? and ?
                                    group by v.matricula,dev.CODVEND, v.nome, pf.CODSITUACAO, v.filial, c.motivo, pf.PERCENTADIANT, pf.CODFUNCAO )a
                        group by a.matricula, a.CODVEND, a.nome, a.CODSITUACAO, a.filial, a.PERCENTADIANT, a.CODFUNCAO ) tot 
                        WHERE tot.NOME NOT LIKE('SAC%') 
                            AND tot.NOME NOT LIKE('CONSUM%') AND tot.NOME NOT LIKE('EXPED%') 
                            AND tot.NOME NOT LIKE('ABAST%') AND tot.NOME NOT LIKE('DUTRA%') 
                            AND tot.CODFUNCAO NOT IN ('577','576','536' , '348', '321', '362', '570', '564', '372', '325', '324', '538', '500', '573', '485', '462', '315', '323', '311', '543', '562', '540', '338', '552', '452', '479', '328', '387', '341', '560', '318', '565', '571', '535')";
                if($request->nome != ''){
                    $sql .= " AND tot.nome like '%$request->nome%' ";
                }
                if($request->mat != ''){
                    $sql .= " AND tot.matricula like '%$request->mat%' ";
                }                                                
                                                                
                $sql .= " GROUP BY tot.matricula, tot.CODVEND, tot.nome, tot.CODSITUACAO, tot.filial, tot.PERCENTADIANT, tot.CODFUNCAO
                order by 3";


                $dados = DB::select($sql, [$request->de, $request->ate, $request->de, $request->ate, $request->de, $request->ate]);
            return $this->success($dados, 'Relatório gerado com sucesso!');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }
}
