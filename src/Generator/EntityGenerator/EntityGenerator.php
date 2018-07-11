<?php

namespace Anytime\ORM\Generator\EntityGenerator;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Entity;

class EntityGenerator implements EntityGeneratorInterface
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
     * @var SnakeToCamelCaseStringConverter
     */
    private $snakeToCamelCaseStringConverter;

    /**
     * @var TableStructureRetrieverInterface
     */
    private $tableStructureRetriever;

    /**
     * EntityGenerator constructor.
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param TableStructureRetrieverInterface $tableStructureRetriever
     * @param string $entityDirectory
     * @param string|null $entityNamespace
     */
    public function __construct(
        SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter,
        TableStructureRetrieverInterface $tableStructureRetriever,
        string $entityDirectory,
        string $entityNamespace = null)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->tableStructureRetriever = $tableStructureRetriever;
        $this
            ->setEntityDirectory($entityDirectory)
            ->setEntityNamespace($entityNamespace)
        ;
    }

    /**
     * @param string $entityDirectory
     * @return EntityGenerator
     */
    public function setEntityDirectory(string $entityDirectory): EntityGenerator
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
     * @return EntityGenerator
     */
    public function setEntityNamespace(string $entityNamespace): EntityGenerator
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
            $entityClassName = ucfirst($this->snakeToCamelCaseStringConverter->convert($tableName));
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
        $primaryKeys = '';
        $className = ucfirst($this->snakeToCamelCaseStringConverter->convert($tableName));

        $propertyDeclarationSourceCode = "    protected \$data = [\n";
        $gettersSettersSourceCode = '';

        $sourceCode  = "<?php \n\n";

        if($this->entityNamespace) {
            $sourceCode .= "namespace ".$this->entityNamespace.";\n\n";
        }

        $sourceCode .= "use ".Entity::class."; \n\n";
        $sourceCode .= "class $className extends Entity\n";
        $sourceCode .= "{\n";
        $sourceCode .= '    const TABLENAME = \''.$tableName.'\''.";\n";

        foreach($tableStruct['structure'] as $fieldStruct) {
            $fieldName = array_key_exists('fieldName', $fieldStruct) ? $fieldStruct['fieldName'] : null;
            $fieldType = array_key_exists('type', $fieldStruct) ? $fieldStruct['type'] : null;
            $nullable = array_key_exists('allowNull', $fieldStruct) ? $fieldStruct['allowNull'] : false;
            $default = array_key_exists('defaultValue', $fieldStruct) ? $fieldStruct['defaultValue'] : null;
            $isPrimary = array_key_exists('keyType', $fieldStruct) && $fieldStruct['keyType'] === 'PRI' ? true : false;

            if($isPrimary) {
                $primaryKeys .= ($primaryKeys ? "','" : '').$fieldName;
            }

            $isString = $fieldType === 'string';

            if($default === 'CURRENT_TIMESTAMP') {
                $default = null;
            }

            // Properties declaration
            $defaultPropertyValue = $this->getDefaultPhpValueByFieldType($fieldType);
            $propertyName = lcfirst($this->snakeToCamelCaseStringConverter->convert($fieldName));
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
            $isDateType = $fieldType === 'date';

            if($isDateType) {
                $typeHintingArg = $nullable ? '' : '\DateTime ';
                $gettersSettersSourceCode .= "    public function set" . ucfirst($propertyName) . '('.$typeHintingArg.'$'.$propertyName.'): '.$className."\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        $this->data[\''. $fieldName .'\'] = $' . $propertyName. '->format(\'Y-m-d H:i:s\');'."\n";
                $gettersSettersSourceCode .= '        if($'.$propertyName.') $this->cachedReturnedObject[__METHOD__] = $'.$propertyName.';'."\n";
                $gettersSettersSourceCode .= '        return $this;'."\n";
                $gettersSettersSourceCode .= "    }\n\n";
            } else {
                $typeHintingArg = $nullable ? '' : $fieldType.' ';
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
                $typeHintingReturn = $nullable ? '' : ': '.$fieldType;
                $gettersSettersSourceCode .= "    public function " . ($fieldType === 'bool' ? 'is' : 'get') . ucfirst($propertyName) . "()$typeHintingReturn\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        return ('.$fieldType.')$this->data[\'' . $fieldName . '\'];'."\n";
                $gettersSettersSourceCode .= "    }\n\n";
            }
        }

        $propertyDeclarationSourceCode .= "    ];\n";

        $primaryKeys = "    const PRIMARY_KEYS = ['" .$primaryKeys. "'];\n";

        $sourceCode .= $primaryKeys;
        $sourceCode .= "\n".$propertyDeclarationSourceCode;
        $sourceCode .= "\n".$gettersSettersSourceCode;
        $sourceCode .= "\n}\n";

        return $sourceCode;
    }

    /**
     * @param string $fieldType
     * @return string
     */
    protected function getDefaultPhpValueByFieldType(string $fieldType)
    {
        switch($fieldType) {
            case 'int': return '0';
            case 'float': return '.0';
            case 'bool': return 'false';
            case 'string':
            default:
                return '';
        }
    }
}
