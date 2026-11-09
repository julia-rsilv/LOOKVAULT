<?php
// Configuração do banco de dados
define('DB_HOST', getenv('LOOKVAULT_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('LOOKVAULT_DB_USER') ?: 'root');
define('DB_PASS', getenv('LOOKVAULT_DB_PASS') ?: '');
define('DB_NAME', getenv('LOOKVAULT_DB_NAME') ?: 'lookvault');

// Conexão com o banco
function conectarDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("LOOKVAULT database connection failed: " . $conn->connect_error);
            throw new Exception("Não foi possível conectar ao banco de dados.");
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("LOOKVAULT database error: " . $e->getMessage());
        http_response_code(500);
        die("Ocorreu um erro interno. Tente novamente mais tarde.");
    }
}

// Função para executar queries com segurança
function executarQuery($sql, $params = []) {
    $conn = conectarDB();

    if (empty($params)) {
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $conn->close();
        return false;
    }

    if (!empty($params)) {
        $types = '';
        $values = [];
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 's';
                $param = (string) $param;
            }
            $values[] = $param;
        }
        // bind_param exige referências para os valores.
        $refs = [];
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        $stmt->bind_param($types, ...$refs);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        return false;
    }

    // SELECT retorna mysqli_result; INSERT/UPDATE/DELETE retornam true
    // quando executados com sucesso. get_result() sozinho retornava false
    // para comandos sem conjunto de resultados, causando falsos erros.
    $result = null;
    if ($stmt->field_count > 0) {
        $result = $stmt->get_result();
    } else {
        $result = true;
    }

    $stmt->close();
    $conn->close();
    return $result;
}

// Função para inserir e retornar o ID
function inserirRetornarId($sql, $params = []) {
    $conn = conectarDB();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        $conn->close();
        return false;
    }
    
    if (!empty($params)) {
        $types = '';
        $values = [];
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $values[] = $param;
        }
        $refs = [];
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        $stmt->bind_param($types, ...$refs);
    }
    
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    
    return $id;
}
?>