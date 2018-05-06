<?php

namespace DVE\EntityORM\Generator\EntityGenerator;

use DVE\EntityORM\Convertor\SnakeToCamelCaseStringConvertor;
use DVE\EntityORM\EntityManager\Entity;

class MySqlEntityGenerator implements EntityGeneratorInterface
{
    /**
     * @var string
     */
    private $entityDirectory;

    /**
     * @var string
     */
    private $entityNamespace;

    /**
     * @var SnakeToCamelCaseStringConvertor
     */
    private $snakeToCamelCaseStringConvertor;

    /**
     * @var TableStructureRetrieverInterface
     */
    private $tableStructureRetriever;

    /**
     * EntityGenerator constructor.
     * @param SnakeToCamelCaseStringConvertor $snakeToCamelCaseStringConvertor
     * @param TableStructureRetrieverInterface $tableStructureRetriever
     * @param string $entityDirectory
     * @param string|null $entityNamespace
     */
    public function __construct(
        SnakeToCamelCaseStringConvertor $snakeToCamelCaseStringConvertor,
        TableStructureRetrieverInterface $tableStructureRetriever,
        string $entityDirectory,
        string $entityNamespace = null)
    {
        $this->snakeToCamelCaseStringConvertor = $snakeToCamelCaseStringConvertor;
        $this->tableStructureRetriever = $tableStructureRetriever;
        $this
            ->setEntityDirectory($entityDirectory)
            ->setEntityNamespace($entityNamespace)
        ;
    }

    /**
     * @param string $entityDirectory
     * @return MySqlEntityGenerator
     */
    public function setEntityDirectory(string $entityDirectory): MySqlEntityGenerator
    {
        if(is_dir($entityDirectory) && is_writable($entityDirectory)) {
            $this->entityDirectory = $entityDirectory;
        } else {
            throw new \RuntimeException('The entity directory should exists and be writable.');
        }
        return $this;
    }

    /**
     * @param string $entityNamespace
     * @return MySqlEntityGenerator
     */
    public function setEntityNamespace(string $entityNamespace): MySqlEntityGenerator
    {
        $entityNamespace = trim($entityNamespace, '\\');

        if(preg_match('/^([a-z0-9_\\\]+)$/i', $entityNamespace)) {
            $this->entityNamespace = $entityNamespace;
        } else {
            throw new \RuntimeException('Invalid entity namespace please use a correct namespace format. Example: My\\Namespace.');
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generate(array $tableList = [])
    {
        foreach($this->tableStructureRetriever->retrieve($tableList) as $tableName => $tableStruct) {
            echo $tableName;
            $entityClassName = ucfirst($this->snakeToCamelCaseStringConvertor->convert($tableName));
            $entityGeneratedSourceCode = $this->generateEntityClassString($tableName, $tableStruct);

            file_put_contents($this->entityDirectory . '/' . $entityClassName . '.php', $entityGeneratedSourceCode);
        }
    }

    /**
     * @param string $tableName
     * @param array $tableStruct
     * @return string
     */
    public function generateEntityClassString(string $tableName, array $tableStruct): string
    {
        $className = ucfirst($this->snakeToCamelCaseStringConvertor->convert($tableName));

        $propertyDeclarationSourceCode = "    protected \$data = [\n";
        $gettersSettersSourceCode = '';

        $sourceCode  = "<?php \n\n";

        if($this->entityNamespace) {
            $sourceCode .= "namespace ".$this->entityNamespace.";\n\n";
        }

        $sourceCode .= "use ".Entity::class."; \n\n";
        $sourceCode .= "class $className extends Entity\n";
        $sourceCode .= "{\n";
        $sourceCode .= '    public $tableName = \''.$tableName.'\''.";\n";

        foreach($tableStruct as $fieldStruct) {
            $fieldName = array_key_exists('Field', $fieldStruct) ? $fieldStruct['Field'] : null;
            $mysqlType = array_key_exists('Type', $fieldStruct) ? $fieldStruct['Type'] : null;
            $nullable = array_key_exists('Null', $fieldStruct) && $fieldStruct['Null'] === 'YES' ? true : false;
            $default = array_key_exists('Default', $fieldStruct) ? $fieldStruct['Default'] : null;
            $phpType = $this->mysqlToPhpType($mysqlType);
            $isString = $phpType === 'string';

            // Properties declaration
            $defaultPropertyValue = $this->getDefaultPhpValueByPhpType($phpType);
            $propertyName = lcfirst($this->snakeToCamelCaseStringConvertor->convert($fieldName));
            $propertyDeclarationSourceCode .=
                "        '$fieldName' => " .
                (!is_null($default)
                    ? ($isString?"'":'') . addslashes($default).($isString?"'":'')
                    : (
                        $nullable
                            ? 'null'
                            : ($isString?"'":'').$defaultPropertyValue.($isString?"'":'')
                    )
                ) .
                ",\n";

            // Setters
            $isDateType = $this->isDateType($mysqlType);
            if($isDateType) {
                $typeHintingArg = $nullable ? '' : '\DateTime ';
                $gettersSettersSourceCode .= "    public function set" . ucfirst($propertyName) . '('.$typeHintingArg.'$'.$propertyName.'): '.$className."\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        $this->data[\''. $fieldName .'\'] = $' . $propertyName. '->format(\'Y-m-d H:i:s\');'."\n";
                $gettersSettersSourceCode .= '        if($'.$propertyName.') $this->cachedReturnedObject[__METHOD__] = $'.$propertyName.';'."\n";
                $gettersSettersSourceCode .= '        return $this;'."\n";
                $gettersSettersSourceCode .= "    }\n\n";
            } else {
                $typeHintingArg = $nullable ? '' : $phpType.' ';
                $gettersSettersSourceCode .= "    public function set" . ucfirst($propertyName) . '('.$typeHintingArg.'$'.$propertyName.'): '.$className."\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        $this->data[\''. $fieldName .'\'] = $' . $propertyName. ';'."\n";
                $gettersSettersSourceCode .= '        return $this;'."\n";
                $gettersSettersSourceCode .= "    }\n\n";
            }

            // Getters
            if($isDateType) {
                $typeHintingReturn = $nullable ? '' : ': \DateTime';
                $gettersSettersSourceCode .= "    public function get" . ucfirst($propertyName) . "()$typeHintingReturn\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        if($this->data[\'' . $fieldName . '\']) {'."\n";
                $gettersSettersSourceCode .= '            return $this->convertDateTimeStringToObject(__METHOD__, (string)$this->data[\'' . $fieldName . '\']);'."\n";
                $gettersSettersSourceCode .= "        }\n";
                $gettersSettersSourceCode .= "    }\n\n";
            } else {
                $typeHintingReturn = $nullable ? '' : ': '.$phpType;
                $gettersSettersSourceCode .= "    public function " . ($phpType === 'bool' ? 'is' : 'get') . ucfirst($propertyName) . "()$typeHintingReturn\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        return ('.$phpType.')$this->data[\'' . $fieldName . '\'];'."\n";
                $gettersSettersSourceCode .= "    }\n\n";
            }
        }

        $propertyDeclarationSourceCode .= "    ];\n";

        $sourceCode .= "\n".$propertyDeclarationSourceCode;
        $sourceCode .= "\n".$gettersSettersSourceCode;
        $sourceCode .= "\n}\n";

        return $sourceCode;
    }

    /**
     * @param string $mysqlType
     * @return int|string
     */
    protected function mysqlToPhpType(string $mysqlType)
    {
        $patterns = [
            'float'     =>  '(decimal|float|double|real)(.*)',
            'bool'      =>  '(boolean|tinyint\(1\))(.*)',
            'int'       =>  '(int|smallint|mediumint|bigint|tinyint)(.*)'
        ];

        foreach($patterns as $phpType => $pattern) {
            if(preg_match('/^'.$pattern.'$/i', $mysqlType)) {
                return $phpType;
            }
        }

        return 'string';
    }

    /**
     * @param string $phpType
     * @return string
     */
    protected function getDefaultPhpValueByPhpType(string $phpType)
    {
        switch($phpType) {
            case 'int': return '0';
            case 'float': return '.0';
            case 'bool': return 'false';
            case 'string':
            default:
                return '';
        }
    }

    /**
     * @param string $mysqlType
     * @return int
     */
    protected function isDateType(string $mysqlType)
    {
        return preg_match('/^(date|datetime|year)$/i', $mysqlType);
    }
}