<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/CategoriaDTO.php';

class CategoriaDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra uma nova categoria
     * @param CategoriaDTO $categoria
     * @return bool|int - retorna o ID da nova categoria ou false em caso de erro
     */
    public function cadastrar(CategoriaDTO $categoria)
    {
        try {
            // Verifica se o nome da categoria já existe
            if ($this->nomeExiste($categoria->getNomeCategoria())) {
                error_log("Categoria com este nome já existe: " . $categoria->getNomeCategoria());
                return false;
            }

            $sql = "INSERT INTO categoria (nomeCategoria, descricao) 
                    VALUES (?, ?)";

            $parametros = [
                $categoria->getNomeCategoria(),
                $categoria->getDescricao()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca uma categoria pelo ID
     * @param int $idCategoria
     * @return CategoriaDTO|null
     */
    public function buscarPorId($idCategoria)
    {
        try {
            $sql = "SELECT * FROM categoria WHERE idCategoria = ?";
            $resultado = $this->bd->buscarUm($sql, [$idCategoria]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca uma categoria pelo nome
     * @param string $nomeCategoria
     * @return CategoriaDTO|null
     */
    public function buscarPorNome($nomeCategoria)
    {
        try {
            $sql = "SELECT * FROM categoria WHERE nomeCategoria = ?";
            $resultado = $this->bd->buscarUm($sql, [$nomeCategoria]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em buscarPorNome(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se o nome da categoria já existe
     * @param string $nomeCategoria
     * @return bool
     */
    public function nomeExiste($nomeCategoria)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM categoria WHERE nomeCategoria = ?";
            $resultado = $this->bd->buscarUm($sql, [$nomeCategoria]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em nomeExiste(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todas as categorias
     * @return array
     */
    public function listarTodas()
    {
        try {
            $sql = "SELECT * FROM categoria ORDER BY nomeCategoria ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $categorias = [];
            foreach ($resultados as $linha) {
                $categorias[] = $this->construirDTO($linha);
            }

            return $categorias;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em listarTodas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista categorias com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT * FROM categoria 
                    ORDER BY nomeCategoria ASC 
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $categorias = [];
            foreach ($resultados as $linha) {
                $categorias[] = $this->construirDTO($linha);
            }

            return $categorias;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca categorias por nome parcial
     * @param string $termo
     * @return array
     */
    public function buscarPorNomeParcial($termo)
    {
        try {
            $sql = "SELECT * FROM categoria 
                    WHERE nomeCategoria LIKE ? 
                    ORDER BY nomeCategoria ASC";

            $termoPesquisa = '%' . $termo . '%';
            $resultados = $this->bd->buscarTodos($sql, [$termoPesquisa]);

            $categorias = [];
            foreach ($resultados as $linha) {
                $categorias[] = $this->construirDTO($linha);
            }

            return $categorias;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em buscarPorNomeParcial(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de categorias
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM categoria";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de uma categoria
     * @param CategoriaDTO $categoria
     * @return bool
     */
    public function atualizar(CategoriaDTO $categoria)
    {
        try {
            $sql = "UPDATE categoria 
                    SET nomeCategoria = ?, descricao = ? 
                    WHERE idCategoria = ?";

            $parametros = [
                $categoria->getNomeCategoria(),
                $categoria->getDescricao(),
                $categoria->getIdCategoria()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o nome de uma categoria
     * @param int $idCategoria
     * @param string $novoNome
     * @return bool
     */
    public function atualizarNome($idCategoria, $novoNome)
    {
        try {
            $sql = "UPDATE categoria SET nomeCategoria = ? WHERE idCategoria = ?";
            $parametros = [$novoNome, $idCategoria];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em atualizarNome(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas a descrição de uma categoria
     * @param int $idCategoria
     * @param string $novaDescricao
     * @return bool
     */
    public function atualizarDescricao($idCategoria, $novaDescricao)
    {
        try {
            $sql = "UPDATE categoria SET descricao = ? WHERE idCategoria = ?";
            $parametros = [$novaDescricao, $idCategoria];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em atualizarDescricao(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga uma categoria
     * @param int $idCategoria
     * @return bool
     */
    public function apagar($idCategoria)
    {
        try {
            $sql = "DELETE FROM categoria WHERE idCategoria = ?";
            $parametros = [$idCategoria];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("CategoriaDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto CategoriaDTO a partir de um array
     * @param array $dados
     * @return CategoriaDTO
     */
    private function construirDTO($dados)
    {
        $categoria = new CategoriaDTO();
        $categoria->setIdCategoria($dados['idCategoria']);
        $categoria->setNomeCategoria($dados['nomeCategoria']);
        $categoria->setDescricao($dados['descricao']);

        return $categoria;
    }
}
