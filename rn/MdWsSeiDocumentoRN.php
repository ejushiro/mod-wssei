<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiDocumentoRN extends InfraRN {

    CONST NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO = 'DOCUMENTO';

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * M�todo que retorna os documentos de um processo
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function listarDocumentosProcessoConectado(DocumentoDTO $documentoDTOParam){
        try{
            $result = array();
            $documentoDTOConsulta = new DocumentoDTO();
            if(!$documentoDTOParam->isSetDblIdProcedimento()){
                throw new InfraException('O procedimento deve ser informado.');
            }
            $documentoDTOConsulta->setDblIdProcedimento($documentoDTOParam->getDblIdProcedimento());
            if($documentoDTOParam->getNumMaxRegistrosRetorno()){
                $documentoDTOConsulta->setNumMaxRegistrosRetorno($documentoDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $documentoDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            if(is_null($documentoDTOParam->getNumPaginaAtual())){
                $documentoDTOConsulta->setNumPaginaAtual(0);
            }else{
                $documentoDTOConsulta->setNumPaginaAtual($documentoDTOParam->getNumPaginaAtual());
            }
            $documentoDTOConsulta->retStrStaNivelAcessoLocalProtocolo();
            $documentoDTOConsulta->retDblIdDocumento();
            $documentoDTOConsulta->retDblIdProcedimento();
            $documentoDTOConsulta->retStrProtocoloDocumentoFormatado();
            $documentoDTOConsulta->retStrNumero();
            $documentoDTOConsulta->retStrNomeSerie();
            $documentoDTOConsulta->retStrSiglaUnidadeGeradoraProtocolo();
            $documentoDTOConsulta->retStrSiglaUnidadeGeradoraProtocolo();
            $documentoDTOConsulta->retNumIdUnidadeGeradoraProtocolo();
            $documentoDTOConsulta->retStrCrcAssinatura();
            $documentoDTOConsulta->retStrStaEstadoProtocolo();
            $documentoRN = new DocumentoRN();
            $ret = $documentoRN->listarRN0008($documentoDTOConsulta);

            $anexoRN = new AnexoRN();
            $observacaoRN = new ObservacaoRN();
            $publicacaoRN = new PublicacaoRN();
            $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            /** @var DocumentoDTO $documentoDTO */
            foreach($ret as $documentoDTO){
                $mimetype = null;
                $nomeAnexo = null;
                $informacao = null;
                $tamanhoAnexo = null;
                $ciencia = 'N';
                $documentoCancelado = $documentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_DOCUMENTO_CANCELADO
                    ? 'S' : 'N';

                $anexoDTOConsulta = new AnexoDTO();
                $anexoDTOConsulta->retStrNome();
                $anexoDTOConsulta->retNumTamanho();
                $anexoDTOConsulta->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
                $anexoDTOConsulta->setStrSinAtivo('S');
                $anexoDTOConsulta->setNumMaxRegistrosRetorno(1);
                $resultAnexo = $anexoRN->listarRN0218($anexoDTOConsulta);
                if($resultAnexo){
                    /** @var AnexoDTO $anexoDTO */
                    $anexoDTO = $resultAnexo[0];
                    $mimetype = $anexoDTO->getStrNome();
                    $mimetype = substr($mimetype, strrpos($mimetype, '.')+1);
                    $nomeAnexo = $anexoDTO->getStrNome();
                    $tamanhoAnexo = $anexoDTO->getNumTamanho();
                }
                $observacaoDTOConsulta = new ObservacaoDTO();
                $observacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
                $observacaoDTOConsulta->setOrdNumIdObservacao(InfraDTO::$TIPO_ORDENACAO_DESC);
                $observacaoDTOConsulta->retStrDescricao();
                $resultObservacao = $observacaoRN->listarRN0219($observacaoDTOConsulta);
                if($resultObservacao){
                    /** @var ObservacaoDTO $observacaoDTO */
                    $observacaoDTO = $resultObservacao[0];
                    $informacao = substr($observacaoDTO->getStrDescricao(), 0, 250);
                }
                $publicacaoDTOConsulta = new PublicacaoDTO();
                $publicacaoDTOConsulta->setDblIdDocumento($documentoDTO->getDblIdDocumento());
                $publicacaoDTOConsulta->retDblIdDocumento();
                $publicacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
                $resultPublicacao = $publicacaoRN->listarRN1045($publicacaoDTOConsulta);
                $documentoPublicado = $resultPublicacao ? 'S' : 'N';

                $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
                $relProtocoloProtocoloDTOConsulta->setDblIdProtocolo2($documentoDTO->getDblIdDocumento());
                $relProtocoloProtocoloDTOConsulta->retStrSinCiencia();
                $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno(1);
                $resultRelProtProt = $relProtocoloProtocoloRN->listarRN0187($relProtocoloProtocoloDTOConsulta);
                if($resultRelProtProt){
                    /** @var RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO */
                    $relProtocoloProtocoloDTO = $resultRelProtProt[0];
                    $ciencia = $relProtocoloProtocoloDTO->getStrSinCiencia();
                }

                $result[] = array(
                    'id' => $documentoDTO->getDblIdDocumento(),
                    'atributos' => array(
                        'idProcedimento' => $documentoDTO->getDblIdProcedimento(),
                        'idProtocolo' => $documentoDTO->getDblIdDocumento(),
                        'protocoloFormatado' => $documentoDTO->getStrProtocoloDocumentoFormatado(),
                        'nome' => $nomeAnexo,
                        'titulo' => $documentoDTO->getStrNumero(),
                        'tipo' => $documentoDTO->getStrNomeSerie(),
                        'mimeType' => $mimetype ? $mimetype : 'html',
                        'informacao' => $informacao,
                        'tamanho' => $tamanhoAnexo,
                        'idUnidade' => $documentoDTO->getNumIdUnidadeGeradoraProtocolo(),
                        'siglaUnidade' => $documentoDTO->getStrSiglaUnidadeGeradoraProtocolo(),
                        'status' => array(
                            'sinBloqueado' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 1 ? 'S' : 'N',
                            'documentoSigiloso' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 2 ? 'S' : 'N',
                            'documentoRestrito' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 1 ? 'S' : 'N',
                            'documentoPublicado' => $documentoPublicado,
                            'documentoAssinado' =>  $documentoDTO->getStrCrcAssinatura() ? 'S' : 'N',
                            'ciencia' => $ciencia,
                            'documentoCancelado' => $documentoCancelado
                        )
                    )
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $documentoDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo simplificado (abstraido) de assinatura de documentos
     * @param string $arrIdDocumento
     * @param int $idOrgao
     * @param string $strCargoFuncao
     * @param string $siglaUsuario
     * @param string $senhaUsuario
     * @param int $idUsuario
     * @return array
     */
    public function apiAssinarDocumentos($arrIdDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario){
        //transforma os dados no array
        if(strpos($arrIdDocumento, ',') !== false) {
            $arrDocs = explode(',', $arrIdDocumento);
        } else {
            $arrDocs = array($arrIdDocumento);
        }
        
        foreach($arrDocs as $dblIdDocumento){
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdDocumento($dblIdDocumento);
            $arrDocumentoDTO[] = $documentoDTO;
        }
        $assinaturaDTO = new AssinaturaDTO();
        $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
        $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
        $assinaturaDTO->setNumIdUsuario($idUsuario);
        $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
        $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
        $assinaturaDTO->setArrObjDocumentoDTO($arrDocumentoDTO);
        return $this->assinarDocumento($assinaturaDTO);
    }

    /**
     * Metodo simplificado (abstraido) de assinatura de documento
     * @param array $arrIdDocumento
     * @param $idOrgao
     * @param $strCargoFuncao
     * @param $siglaUsuario
     * @param $senhaUsuario
     * @param $idUsuario
     * @return array
     */
    public function apiAssinarDocumento($idDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario){
        $arrDocumentoDTO = array();
        $documentoDTO = new DocumentoDTO();
        $documentoDTO->setDblIdDocumento($idDocumento);
        $arrDocumentoDTO[] = $documentoDTO;
        $assinaturaDTO = new AssinaturaDTO();
        $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
        $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
        $assinaturaDTO->setNumIdUsuario($idUsuario);
        $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
        $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
        $assinaturaDTO->setArrObjDocumentoDTO($arrDocumentoDTO);
        return $this->assinarDocumento($assinaturaDTO);
    }

    /**
     * Realizar Assinatura Eletr?nica
     * @param AssinaturaDTO $assinaturaDTO
     * @return array
     */
    public function assinarDocumentoControlado(AssinaturaDTO $assinaturaDTO){
        try{
            $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
            $assinaturaDTO->setNumIdContextoUsuario(null);
            $documentoRN = new DocumentoRN();
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento em bloco assinado com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * @param DocumentoDTO $documentoDTO
     *   id documento obrigatorio
     * @return array
     */
    protected function darCienciaControlado(DocumentoDTO $documentoDTO){
        try{
            $documentoRN = new DocumentoRN();
            if(!$documentoDTO->isSetDblIdDocumento()){
                throw new InfraException('O documento n�o foi informado.');
            }
            $documentoRN->darCiencia($documentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Ci�ncia documento realizado com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    protected function downloadAnexoConectado(ProtocoloDTO $protocoloDTOParam){
        try{
            if(!$protocoloDTOParam->isSetDblIdProtocolo() || !$protocoloDTOParam->getDblIdProtocolo()){
                throw new InfraException('O protocolo deve ser informado!');
            }
            $documentoDTOConsulta = new DocumentoDTO();
            $documentoDTOConsulta->setDblIdProtocoloProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $documentoDTOConsulta->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
            $documentoDTOConsulta->retDblIdDocumento();
            $documentoDTOConsulta->retStrConteudo();
            $documentoDTOConsulta->retStrStaDocumento();
            $documentoDTOConsulta->retStrConteudoAssinatura();
            $documentoBD = new DocumentoRN();
            $resultDocumento = $documentoBD->listarRN0008($documentoDTOConsulta);

            if(!empty($resultDocumento)){
                /** @var DocumentoDTO $documentoDTO */
                $documentoDTO = $resultDocumento[0];
                $editorDTOConsulta = new EditorDTO();
                $editorDTOConsulta->setDblIdDocumento($documentoDTO->getDblIdDocumento());
                $editorDTOConsulta->setNumIdBaseConhecimento(null);
                $editorDTOConsulta->setStrSinCabecalho('S');
                $editorDTOConsulta->setStrSinRodape('S');
                $editorDTOConsulta->setStrSinIdentificacaoVersao('S');
                $editorDTOConsulta->setStrSinProcessarLinks('S');
                $editorRN = new EditorRN();
                $html = $editorRN->consultarHtmlVersao($editorDTOConsulta);

                $auditoriaProtocoloDTO = new AuditoriaProtocoloDTO();
                $auditoriaProtocoloDTO->setStrRecurso('documento_visualizar');
                $auditoriaProtocoloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $auditoriaProtocoloDTO->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
                $auditoriaProtocoloDTO->setNumIdAnexo(null);
                $auditoriaProtocoloDTO->setDtaAuditoria(InfraData::getStrDataAtual());
                $auditoriaProtocoloDTO->setNumVersao($editorDTOConsulta->getNumVersao());

                $auditoriaProtocoloRN = new AuditoriaProtocoloRN();
                $auditoriaProtocoloRN->auditarVisualizacao($auditoriaProtocoloDTO);

                return MdWsSeiRest::formataRetornoSucessoREST(null, array('html' => $html));
            }

            $anexoDTO = new AnexoDTO();
            $anexoDTO->retNumIdAnexo();
            $anexoDTO->retDthInclusao();
            $anexoDTO->retDthInclusao();
            $anexoDTO->retStrNome();
            $anexoDTO->retStrHash();
            $anexoDTO->setDblIdProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $anexoDTO->setStrSinAtivo('S');
            $anexoRN = new AnexoRN();
            $resultAnexo = $anexoRN->listarRN0218($anexoDTO);
            if(empty($resultAnexo)){
                throw new InfraException('Documento n�o encontrado!');
            }
            $anexo = $resultAnexo[0];
            SeiINT::download($anexo);
            exit;
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Listar Ciencias realizadas em um Documento
     * @param MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam
     *   *valor = protocoloformatado?
     * @return array
     */
    protected function listarCienciaDocumentoConectado(MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam){
        try{
            if(!$mdWsSeiProcessoDTOParam->isSetStrValor()){
                throw new InfraException('N�mero do documento n�o informado.');
            }

            $result = array();
            $atributoAndamentoDTOConsulta = new AtributoAndamentoDTO();
            $atributoAndamentoDTOConsulta->retTodos();
            $atributoAndamentoDTOConsulta->retDthAberturaAtividade();
            $atributoAndamentoDTOConsulta->retStrSiglaUsuarioOrigemAtividade();
            $atributoAndamentoDTOConsulta->retStrSiglaUnidadeOrigemAtividade();
            $atributoAndamentoDTOConsulta->setNumIdTarefaAtividade(TarefaRN::$TI_DOCUMENTO_CIENCIA);
            $atributoAndamentoDTOConsulta->setStrValor($mdWsSeiProcessoDTOParam->getStrValor());
            $atributoAndamentoDTOConsulta->setStrNome(self::NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO);
            $atributoAndamentoRN = new AtributoAndamentoRN();
            $ret = $atributoAndamentoRN->listarRN1367($atributoAndamentoDTOConsulta);
            $tarefaDTO = new TarefaDTO();
            $tarefaDTO->retStrNome();
            $tarefaDTO->setNumIdTarefa($atributoAndamentoDTOConsulta->getNumIdTarefaAtividade());
            $tarefaRN = new TarefaRN();
            $tarefaDTO = $tarefaRN->consultar($tarefaDTO);
            $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();
            /** @var AtributoAndamentoDTO $atributoAndamentoDTO */
            foreach($ret as $atributoAndamentoDTO){
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setNumIdAtividade($atributoAndamentoDTO->getNumIdAtividade());
                $mdWsSeiProcessoDTO->setStrTemplate($tarefaDTO->getStrNome());
                $result[] = array(
                    'data' => $atributoAndamentoDTO->getDthAberturaAtividade(),
                    'unidade' => $atributoAndamentoDTO->getStrSiglaUnidadeOrigemAtividade(),
                    'nome' => $atributoAndamentoDTO->getStrSiglaUsuarioOrigemAtividade(),
                    'descricao' => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Listar assinaturas do documento
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function listarAssinaturasDocumentoConectado(DocumentoDTO $documentoDTOParam){
        try{
            if(!$documentoDTOParam->isSetDblIdDocumento()){
                throw new InfraException('O documento n�o foi informado.');
            }

            $result = array();
            $assinaturaDTOConsulta = new AssinaturaDTO();
            $assinaturaDTOConsulta->retTodos();
            $assinaturaDTOConsulta->retStrSiglaUnidade();
            $assinaturaDTOConsulta->setDblIdDocumento($documentoDTOParam->getDblIdDocumento());
            $assinaturaRN = new AssinaturaRN();
            $ret = $assinaturaRN->listarRN1323($assinaturaDTOConsulta);
            /** @var AssinaturaDTO $assinaturaDTO */
            foreach($ret as $assinaturaDTO){
                $result[] = array(
                    'nome' => $assinaturaDTO->getStrNome(),
                    'cargo' => $assinaturaDTO->getStrTratamento(),
                    'unidade' => $assinaturaDTO->getStrSiglaUnidade()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}