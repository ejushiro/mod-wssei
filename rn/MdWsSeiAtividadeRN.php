<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAtividadeRN extends AtividadeRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna as atividades de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     * @throws InfraException
     */
    protected function listarAtividadesProcessoConectado(AtividadeDTO $atividadeDTOParam){
        try{
            $result = array();
            $atividadeDTOConsulta = new AtividadeDTO();
            if(!$atividadeDTOParam->isSetDblIdProtocolo()){
                throw new InfraException('O procedimento deve ser informado!');
            }
            $atividadeDTOConsulta->setDblIdProtocolo($atividadeDTOParam->getDblIdProtocolo());
            if(is_null($atividadeDTOParam->getNumPaginaAtual())){
                $atividadeDTOConsulta->setNumPaginaAtual(0);
            }else{
                $atividadeDTOConsulta->setNumPaginaAtual($atividadeDTOParam->getNumPaginaAtual());
            }
            if($atividadeDTOParam->getNumMaxRegistrosRetorno()){
                $atividadeDTOConsulta->setNumMaxRegistrosRetorno($atividadeDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $atividadeDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            $atividadeDTOConsulta->retDblIdProtocolo();
            $atividadeDTOConsulta->retDthAbertura();
            $atividadeDTOConsulta->retNumIdUsuarioOrigem();
            $atividadeDTOConsulta->retStrNomeTarefa();
            $atividadeDTOConsulta->retNumIdAtividade();
            $atividadeDTOConsulta->retStrSiglaUsuarioOrigem();
            $atividadeDTOConsulta->retStrSiglaUnidade();
            $atividadeDTOConsulta->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            /** @var AtividadeDTO $atividadeDTO */
            foreach($ret as $atividadeDTO) {
                $dateTime = explode(' ', $atividadeDTO->getDthAbertura());
                $informacao = null;
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setStrTemplate($atividadeDTO->getStrNomeTarefa());
                $mdWsSeiProcessoDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();

                $result[] = [
                    "id" => $atividadeDTO->getNumIdAtividade(),
                    "atributos" => [
                        "idProcesso" => $atividadeDTO->getDblIdProtocolo(),
                        "usuario" => ($atividadeDTO->getNumIdUsuarioOrigem())? $atividadeDTO->getStrSiglaUsuarioOrigem() : null,
                        "data" => $dateTime[0],
                        "hora" => $dateTime[1],
                        "unidade" => $atividadeDTO->getStrSiglaUnidade(),
                        "informacao" => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                    ]
                ];
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $atividadeDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que encapsula os dados para o cadastramento do andamento do processo
     * @param array $post
     * @return AtualizarAndamentoDTO
     */
    public function encapsulaLancarAndamentoProcesso(array $data){
        $entradaLancarAndamentoAPI = new EntradaLancarAndamentoAPI();
        $entradaLancarAndamentoAPI->setIdTarefa(TarefaRN::$TI_ATUALIZACAO_ANDAMENTO);
        if($data['protocolo']){
            $entradaLancarAndamentoAPI->setIdProcedimento($data['protocolo']);
        }

        if($data['descricao']){
            $atributoAndamentoAPI = new AtributoAndamentoAPI();
            $atributoAndamentoAPI->setNome('DESCRICAO');
            $atributoAndamentoAPI->setValor($data['descricao']);
            $atributoAndamentoAPI->setIdOrigem(null);
            $entradaLancarAndamentoAPI->setAtributos(array($atributoAndamentoAPI));
        }

        return $entradaLancarAndamentoAPI;
    }

    /**
     * Método que cadastra o andamento manual de um processo
     * @param EntradaLancarAndamentoAPI $entradaLancarAndamentoAPIParam
     * @info usar o método auxiliar encapsulaLancarAndamentoProcesso para faciliar
     * @return array
     */
    protected function lancarAndamentoProcessoControlado(EntradaLancarAndamentoAPI $entradaLancarAndamentoAPIParam){
        try{
            $seiRN = new SeiRN();
            $seiRN->lancarAndamento($entradaLancarAndamentoAPIParam);

            return MdWsSeiRest::formataRetornoSucessoREST('Observação cadastrada com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método clonado de AtividadeRN::listarPendenciasRN0754Conectado com alterações para pesquisa de processo
     * @param MdWsSeiPesquisarPendenciaDTO $objPesquisaPendenciaDTO
     * @return array
     * @throws InfraException
     */
    protected function listarPendenciasConectado(MdWsSeiPesquisarPendenciaDTO $objPesquisaPendenciaDTO) {
        try {
            if (!$objPesquisaPendenciaDTO->isSetStrStaTipoAtribuicao()) {
                $objPesquisaPendenciaDTO->setStrStaTipoAtribuicao(self::$TA_TODAS);
            }

            if (!$objPesquisaPendenciaDTO->isSetNumIdUsuarioAtribuicao()) {
                $objPesquisaPendenciaDTO->setNumIdUsuarioAtribuicao(null);
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinMontandoArvore()) {
                $objPesquisaPendenciaDTO->setStrSinMontandoArvore('N');
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinAnotacoes()) {
                $objPesquisaPendenciaDTO->setStrSinAnotacoes('N');
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinSituacoes()) {
                $objPesquisaPendenciaDTO->setStrSinSituacoes('N');
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinMarcadores()) {
                $objPesquisaPendenciaDTO->setStrSinMarcadores('N');
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinInteressados()) {
                $objPesquisaPendenciaDTO->setStrSinInteressados('N');
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinRetornoProgramado()) {
                $objPesquisaPendenciaDTO->setStrSinRetornoProgramado('N');
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinCredenciais()) {
                $objPesquisaPendenciaDTO->setStrSinCredenciais('N');
            }

            if (!$objPesquisaPendenciaDTO->isSetStrSinHoje()) {
                $objPesquisaPendenciaDTO->setStrSinHoje('N');
            }

            $objAtividadeDTO = new MdWsSeiAtividadeDTO();
            $objAtividadeDTO->retNumIdAtividade();
            $objAtividadeDTO->retNumIdTarefa();
            $objAtividadeDTO->retNumIdUsuarioAtribuicao();
            $objAtividadeDTO->retNumIdUsuarioVisualizacao();
            $objAtividadeDTO->retNumTipoVisualizacao();
            $objAtividadeDTO->retNumIdUnidade();
            $objAtividadeDTO->retDthConclusao();
            $objAtividadeDTO->retDblIdProtocolo();
            $objAtividadeDTO->retStrSiglaUnidade();
            $objAtividadeDTO->retStrSinInicial();
            $objAtividadeDTO->retNumIdUsuarioAtribuicao();
            $objAtividadeDTO->retStrSiglaUsuarioAtribuicao();
            $objAtividadeDTO->retStrNomeUsuarioAtribuicao();

            $objAtividadeDTO->setNumIdUnidade($objPesquisaPendenciaDTO->getNumIdUnidade());

            if ($objPesquisaPendenciaDTO->isSetStrProtocoloFormatadoPesquisaProtocolo()) {
                $strProtocoloFormatado = InfraUtil::retirarFormatacao(
                    $objPesquisaPendenciaDTO->getStrProtocoloFormatadoPesquisaProtocolo(), false
                );
                $objAtividadeDTO->setStrProtocoloFormatadoPesquisaProtocolo(
                    '%'.$strProtocoloFormatado.'%',
                    InfraDTO::$OPER_LIKE
                );
            }
            if ($objPesquisaPendenciaDTO->isSetNumIdGrupoAcompanhamentoProcedimento()) {
                $objAtividadeDTO->setNumIdGrupoAcompanhamentoProcedimento($objPesquisaPendenciaDTO->getNumIdGrupoAcompanhamentoProcedimento());
            }

            if ($objPesquisaPendenciaDTO->getStrSinHoje() == 'N') {
                $objAtividadeDTO->setDthConclusao(null);
            } else {
                $objAtividadeDTO->adicionarCriterio(array('Conclusao', 'Conclusao'),
                    array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_MAIOR_IGUAL),
                    array(null, InfraData::getStrDataAtual() . ' 00:00:00'),
                    array(InfraDTO::$OPER_LOGICO_OR));
            }

            $objAtividadeDTO->setStrStaProtocoloProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);

            if ($objPesquisaPendenciaDTO->getNumIdUsuario() == null) {
                $objAtividadeDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_SIGILOSO, InfraDTO::$OPER_DIFERENTE);
            } else {
                $objAtividadeDTO->adicionarCriterio(array('StaNivelAcessoGlobalProtocolo', 'IdUsuario'),
                    array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IGUAL),
                    array(ProtocoloRN::$NA_SIGILOSO, $objPesquisaPendenciaDTO->getNumIdUsuario()),
                    array(InfraDTO::$OPER_LOGICO_OR));
            }

            if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_MINHAS) {
                $objAtividadeDTO->setNumIdUsuarioAtribuicao($objPesquisaPendenciaDTO->getNumIdUsuario());
            } else if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_DEFINIDAS) {
                $objAtividadeDTO->setNumIdUsuarioAtribuicao(null, InfraDTO::$OPER_DIFERENTE);
            } else if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_ESPECIFICAS) {
                $objAtividadeDTO->setNumIdUsuarioAtribuicao($objPesquisaPendenciaDTO->getNumIdUsuarioAtribuicao());
            }

            if ($objPesquisaPendenciaDTO->isSetDblIdProtocolo()) {
                if (!is_array($objPesquisaPendenciaDTO->getDblIdProtocolo())) {
                    $objAtividadeDTO->setDblIdProtocolo($objPesquisaPendenciaDTO->getDblIdProtocolo());
                } else {
                    $objAtividadeDTO->setDblIdProtocolo($objPesquisaPendenciaDTO->getDblIdProtocolo(), InfraDTO::$OPER_IN);
                }
            }

            if ($objPesquisaPendenciaDTO->isSetStrStaEstadoProcedimento()) {
                if (is_array($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento())) {
                    $objAtividadeDTO->setStrStaEstadoProtocolo($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento(), InfraDTO::$OPER_IN);
                } else {
                    $objAtividadeDTO->setStrStaEstadoProtocolo($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento());
                }
            }

            if ($objPesquisaPendenciaDTO->isSetStrSinInicial()) {
                $objAtividadeDTO->setStrSinInicial($objPesquisaPendenciaDTO->getStrSinInicial());
            }

            if ($objPesquisaPendenciaDTO->isSetNumIdMarcador()) {
                $objAtividadeDTO->setNumTipoFkAndamentoMarcador(InfraDTO::$TIPO_FK_OBRIGATORIA);
                $objAtividadeDTO->setNumIdMarcador($objPesquisaPendenciaDTO->getNumIdMarcador());
                $objAtividadeDTO->setStrSinUltimoAndamentoMarcador('S');
            }

            //ordenar pela data de abertura descendente
            //$objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
            $objAtividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);


            //paginação
            $objAtividadeDTO->setNumMaxRegistrosRetorno($objPesquisaPendenciaDTO->getNumMaxRegistrosRetorno());
            $objAtividadeDTO->setNumPaginaAtual($objPesquisaPendenciaDTO->getNumPaginaAtual());

            $arrAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

            //paginação
            $objPesquisaPendenciaDTO->setNumTotalRegistros($objAtividadeDTO->getNumTotalRegistros());
            $objPesquisaPendenciaDTO->setNumRegistrosPaginaAtual($objAtividadeDTO->getNumRegistrosPaginaAtual());

            $arrProcedimentos = array();

            //Se encontrou pelo menos um registro
            if (count($arrAtividadeDTO) > 0) {

                $objProcedimentoDTO = new ProcedimentoDTO();

                $objProcedimentoDTO->retStrDescricaoProtocolo();

                $arrProtocolosAtividades = array_unique(InfraArray::converterArrInfraDTO($arrAtividadeDTO, 'IdProtocolo'));
                $objProcedimentoDTO->setDblIdProcedimento($arrProtocolosAtividades, InfraDTO::$OPER_IN);

                if ($objPesquisaPendenciaDTO->getStrSinMontandoArvore() == 'S') {
                    $objProcedimentoDTO->setStrSinMontandoArvore('S');
                }

                if ($objPesquisaPendenciaDTO->getStrSinAnotacoes() == 'S') {
                    $objProcedimentoDTO->setStrSinAnotacoes('S');
                }

                if ($objPesquisaPendenciaDTO->getStrSinSituacoes() == 'S') {
                    $objProcedimentoDTO->setStrSinSituacoes('S');
                }

                if ($objPesquisaPendenciaDTO->getStrSinMarcadores() == 'S') {
                    $objProcedimentoDTO->setStrSinMarcadores('S');
                }

                if ($objPesquisaPendenciaDTO->isSetDblIdDocumento()) {
                    $objProcedimentoDTO->setArrDblIdProtocoloAssociado(array($objPesquisaPendenciaDTO->getDblIdDocumento()));
                }

                $objProcedimentoRN = new ProcedimentoRN();

                $arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);

                $arrObjParticipanteDTO = null;
                if ($objPesquisaPendenciaDTO->getStrSinInteressados() == 'S') {

                    $arrObjParticipanteDTO = array();

                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->retDblIdProtocolo();
                    $objParticipanteDTO->retStrSiglaContato();
                    $objParticipanteDTO->retStrNomeContato();
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                    $objParticipanteDTO->setDblIdProtocolo($arrProtocolosAtividades, InfraDTO::$OPER_IN);

                    $objParticipanteRN = new ParticipanteRN();
                    $arrTemp = $objParticipanteRN->listarRN0189($objParticipanteDTO);

                    foreach ($arrTemp as $objParticipanteDTO) {
                        if (!isset($arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()])) {
                            $arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()] = array($objParticipanteDTO);
                        } else {
                            $arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()][] = $objParticipanteDTO;
                        }
                    }
                }

                $arrObjRetornoProgramadoDTO = null;
                if ($objPesquisaPendenciaDTO->getStrSinRetornoProgramado() == 'S') {
                    $objRetornoProgramadoDTO = new RetornoProgramadoDTO();
                    $objRetornoProgramadoDTO->retDblIdProtocoloAtividadeEnvio();
                    $objRetornoProgramadoDTO->retStrSiglaUnidadeOrigemAtividadeEnvio();
                    $objRetornoProgramadoDTO->retDtaProgramada();
                    $objRetornoProgramadoDTO->setNumIdUnidadeAtividadeEnvio($objPesquisaPendenciaDTO->getNumIdUnidade());
                    $objRetornoProgramadoDTO->setDblIdProtocoloAtividadeEnvio($arrProtocolosAtividades, InfraDTO::$OPER_IN);
                    $objRetornoProgramadoDTO->setNumIdAtividadeRetorno(null);

                    $objRetornoProgramadoRN = new RetornoProgramadoRN();
                    $arrObjRetornoProgramadoDTO = InfraArray::indexarArrInfraDTO($objRetornoProgramadoRN->listar($objRetornoProgramadoDTO), 'IdProtocoloAtividadeEnvio', true);
                }


                //Manter ordem obtida na listagem das atividades
                $arrAdicionados = array();
                $arrIdProcedimentoSigiloso = array();

                $arr = InfraArray::indexarArrInfraDTO($arr, 'IdProcedimento');

                foreach ($arrAtividadeDTO as $objAtividadeDTO) {

                    $objProcedimentoDTO = $arr[$objAtividadeDTO->getDblIdProtocolo()];

                    //pode não existir se o procedimento foi excluído
                    if ($objProcedimentoDTO != null) {

                        $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

                        if ($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO) {

                            $objProcedimentoDTO->setStrSinCredencialProcesso('N');
                            $objProcedimentoDTO->setStrSinCredencialAssinatura('N');

                            $arrIdProcedimentoSigiloso[] = $dblIdProcedimento;
                        }

                        if (!isset($arrAdicionados[$dblIdProcedimento])) {

                            $objProcedimentoDTO->setArrObjAtividadeDTO(array($objAtividadeDTO));

                            if (is_array($arrObjParticipanteDTO)) {
                                if (isset($arrObjParticipanteDTO[$dblIdProcedimento])) {
                                    $objProcedimentoDTO->setArrObjParticipanteDTO($arrObjParticipanteDTO[$dblIdProcedimento]);
                                } else {
                                    $objProcedimentoDTO->setArrObjParticipanteDTO(null);
                                }
                            }

                            if (is_array($arrObjRetornoProgramadoDTO)) {
                                if (isset($arrObjRetornoProgramadoDTO[$dblIdProcedimento])) {
                                    $objProcedimentoDTO->setArrObjRetornoProgramadoDTO($arrObjRetornoProgramadoDTO[$dblIdProcedimento]);
                                } else {
                                    $objProcedimentoDTO->setArrObjRetornoProgramadoDTO(null);
                                }
                            }

                            $arrProcedimentos[] = $objProcedimentoDTO;
                            $arrAdicionados[$dblIdProcedimento] = 0;
                        } else {
                            $arrAtividadeDTOProcedimento = $objProcedimentoDTO->getArrObjAtividadeDTO();
                            $arrAtividadeDTOProcedimento[] = $objAtividadeDTO;
                            $objProcedimentoDTO->setArrObjAtividadeDTO($arrAtividadeDTOProcedimento);
                        }
                    }
                }


                if ($objPesquisaPendenciaDTO->getStrSinCredenciais() == 'S' && count($arrIdProcedimentoSigiloso)) {

                    $objAcessoDTO = new AcessoDTO();
                    $objAcessoDTO->retDblIdProtocolo();
                    $objAcessoDTO->retStrStaTipo();
                    $objAcessoDTO->setNumIdUsuario($objPesquisaPendenciaDTO->getNumIdUsuario());
                    $objAcessoDTO->setNumIdUnidade($objPesquisaPendenciaDTO->getNumIdUnidade());
                    $objAcessoDTO->setStrStaTipo(array(AcessoRN::$TA_CREDENCIAL_PROCESSO, AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO), InfraDTO::$OPER_IN);
                    $objAcessoDTO->setDblIdProtocolo($arrIdProcedimentoSigiloso, InfraDTO::$OPER_IN);

                    $objAcessoRN = new AcessoRN();
                    $arrObjAcessoDTO = $objAcessoRN->listar($objAcessoDTO);

                    /*
                    foreach($arr as $objProcedimentoDTO){
                      $objProcedimentoDTO->setStrSinCredencialProcesso('N');
                      $objProcedimentoDTO->setStrSinCredencialAssinatura('N');
                    }
                    */

                    foreach ($arrObjAcessoDTO as $objAcessoDTO) {
                        if ($objAcessoDTO->getStrStaTipo() == AcessoRN::$TA_CREDENCIAL_PROCESSO) {
                            $arr[$objAcessoDTO->getDblIdProtocolo()]->setStrSinCredencialProcesso('S');
                        } else if ($objAcessoDTO->getStrStaTipo() == AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO) {
                            $arr[$objAcessoDTO->getDblIdProtocolo()]->setStrSinCredencialAssinatura('S');
                        }
                    }

                }
            }

            return $arrProcedimentos;

        } catch (Exception $e) {
            throw new InfraException('Erro recuperando processos abertos.', $e);
        }
    }



}