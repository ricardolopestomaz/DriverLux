<?php

use PHPUnit\Framework\TestCase;

// Importa o Model que vamos testar
// Ajuste o caminho se a sua pasta de Models tiver um nome diferente
require_once __DIR__ . '/../app/Model/UsuarioModel.php';

class UsuarioModelTest extends TestCase {

    private $dbMock;
    private $stmtMock;
    private $model;

    protected function setUp(): void {
        // 1. Criamos um mock da classe nativa PDO (o gerenciador do banco)
        $this->dbMock = $this->createMock(PDO::class);

        // 2. Criamos um mock da classe PDOStatement (o executor de queries)
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // 3. Instanciamos o Model injetando o banco fantoche
        $this->model = new UsuarioModel($this->dbMock);
    }

    public function testFindAllRetornaListaDeUsuarios() {
        $dadosFalsos = [
            ["id" => 1, "nome" => "Ana Moura", "email" => "ana@driverlux.com"],
            ["id" => 2, "nome" => "João Silva", "email" => "joao@driverlux.com"]
        ];

        // Configura o PDO para retornar o Statement mockado quando 'prepare' for chamado
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);
        
        // Configura o Statement para dizer que a execução deu certo
        $this->stmtMock->method('execute')->willReturn(true);
        
        // Configura o fetchAll para devolver nossos dados falsos
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($dadosFalsos);

        // Executa o método real do Model
        $resultado = $this->model->findAll();

        // Asserções
        $this->assertCount(2, $resultado);
        $this->assertEquals("Ana Moura", $resultado[0]['nome']);
    }

    public function testFindByIdRetornaUsuarioEspecifico() {
        $usuarioFalso = ["id" => 5, "nome" => "Carlos", "email" => "carlos@email.com"];

        $this->dbMock->method('prepare')->willReturn($this->stmtMock);
        
        // Garante que o bindParam será chamado vinculando o ID correto
        $this->stmtMock->expects($this->once())
                       ->method('bindParam')
                       ->with(":id", $this->equalTo(5));
                       
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->with(PDO::FETCH_ASSOC)->willReturn($usuarioFalso);

        $resultado = $this->model->findById(5);

        $this->assertEquals("Carlos", $resultado['nome']);
        $this->assertEquals(5, $resultado['id']);
    }

    public function testCreateInsereUsuarioComSucesso() {
        // Monta o objeto de dados simulando o que vem do formulário/controller
        $dadosInsercao = (object) [
            "nome" => "Lucas",
            "cpf" => "123.456.789-00",
            "email" => "lucas@email.com",
            "perfil" => "cliente"
        ];
        $senhaHashFalsa = "hash_secreta_123";

        $this->dbMock->method('prepare')->willReturn($this->stmtMock);
        
        // Verifica se os parâmetros estão sendo devidamente amarrados (bouded)
        $this->stmtMock->method('bindParam')->willReturn(true);
        
        // Força o execute a retornar true (simulando inserção realizada)
        $this->stmtMock->method('execute')->willReturn(true);

        $resultado = $this->model->create($dadosInsercao, $senhaHashFalsa);

        // Deve retornar true se salvou
        $this->assertTrue($resultado);
    }

    public function testUpdateAtualizaDadosEDevolveQuantidadeDeLinhasAfetadas() {
        $id = 1;
        $campos = ["nome = :nome", "email = :email"];
        $parametros = [":nome" => "Ana Editado", ":email" => "ana_nova@email.com", ":id" => $id];

        $this->dbMock->method('prepare')->willReturn($this->stmtMock);
        
        // Garante que o execute receberá os parâmetros do update mapeados corretamente
        $this->stmtMock->expects($this->once())
                       ->method('execute')
                       ->with($this->equalTo($parametros))
                       ->willReturn(true);

        // Simula que 1 linha foi modificada no banco
        $this->stmtMock->method('rowCount')->willReturn(1);

        $linhasAfetadas = $this->model->update($id, $campos, $parametros);

        $this->assertEquals(1, $linhasAfetadas);
    }
}