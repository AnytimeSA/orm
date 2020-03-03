<?php

namespace Anytime\ORM\Generator\EntityGenerator;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Entity;
use Anytime\ORM\EntityManager\FilterCollection;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var FilterCollection
     */
    private $filterCollection;

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
        FilterCollection $filterCollection,
        string $entityDirectory,
        string $entityNamespace = null)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->tableStructureRetriever = $tableStructureRetriever;
        $this->filterCollection = $filterCollection;
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
        $this->entityDirectory = $entityDirectory;

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
    public function generate(array $tableList = [], array $ignoredTables = [])
    {
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($this->entityDirectory);

        foreach($this->tableStructureRetriever->retrieve($tableList) as $tableName => $tableStruct) {
            if (in_array($tableName, $ignoredTables)) {
                continue;
            }

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
        $alreadyGeneratedGetterSetter = []; // used to avoid setter/getter name conflict in canse of similar fields like : myfield and my_field, we add  "_2" ... "_3" ... o the end of the setter/getter

        $primaryKeys = '';
        $className = ucfirst($this->snakeToCamelCaseStringConverter->convert($tableName));

        $propertyDeclarationSourceCode = "    protected \$data = [\n";
        $dataSetterUsedSourceCode = '    protected $dataSetterUsed = ['."\n";
        $sqlFieldStructSourceCode = '    protected static $sqlFieldStruct = ['."\n";
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
            $dateForrmat =  array_key_exists('dateFormat', $fieldStruct) ? $fieldStruct['dateFormat'] : 'Y-m-d H:i:s';

            if ($isPrimary) {
                $primaryKeys .= ($primaryKeys ? "','" : '') . $fieldName;
            }

            $isString = $fieldType === 'string' || $fieldType === 'date';

            // Properties declaration
            $defaultPropertyValue = $this->getDefaultPhpValueByFieldType($fieldType);
            $propertyName = lcfirst($this->snakeToCamelCaseStringConverter->convert($fieldName));
            $quote = ($isString ? "'" : '');

            if(is_null($default) && !$nullable) {
                $propertyValueCode = $quote . $defaultPropertyValue . $quote;
            } else {
                $propertyValueCode = 'null';
            }

            $propertyDeclarationSourceCode .=
                "        '$fieldName' => " . $propertyValueCode .
                ",\n";

            // dataSetterUsed
            $dataSetterUsedSourceCode .= "        '$fieldName' => false,\n";

            // fields types
            $sqlFieldStructSourceCode .= "        '$fieldName' => ".str_replace("\n", "", var_export($fieldStruct, true)).",\n";


            // Setters
            $isDateType = $fieldType === 'date';
            $setterName = "set" . ucfirst($propertyName);
            $setterSuffix = '';

            if(array_key_exists(strtolower($setterName), $alreadyGeneratedGetterSetter)) {
                $setterSuffix = '_'.($alreadyGeneratedGetterSetter[strtolower($setterName)]+1);
            }


            if($isDateType) {
                $typeHintingArg = $nullable ? '' : '\DateTime ';

                $gettersSettersSourceCode .= "    /**\n";
                $gettersSettersSourceCode .= "     * @param \\DateTime".($nullable ? '|null' : '')." \$$propertyName\n";
                $gettersSettersSourceCode .= "     * @return $className\n";
                $gettersSettersSourceCode .= "     */\n";

                $gettersSettersSourceCode .= "    public function " . $setterName . $setterSuffix . '('.$typeHintingArg.'$'.$propertyName.'): '.$className."\n";
                $gettersSettersSourceCode .= "    {\n";

                if($nullable) {
                    $gettersSettersSourceCode .= "        if(is_object(\$$propertyName) && get_class(\$$propertyName) === 'DateTime') {\n";
                }

                $gettersSettersSourceCode .= '            $this->dataSetterUsed[\''. $fieldName .'\'] = true;'."\n";
                $gettersSettersSourceCode .= '            $this->data[\''. $fieldName .'\'] = $' . $propertyName. '->format(\''.$dateForrmat.'\');'."\n";
                $gettersSettersSourceCode .= '            $this->cachedReturnedObject[__METHOD__] = $'.$propertyName.';'."\n";

                if($nullable) {
                    $gettersSettersSourceCode .= "        } else {\n";
                    $gettersSettersSourceCode .= '            $this->dataSetterUsed[\''. $fieldName .'\'] = true;'."\n";
                    $gettersSettersSourceCode .= '            $this->data[\''. $fieldName .'\'] = null;'."\n";
                    $gettersSettersSourceCode .= "        }\n";
                }

                $gettersSettersSourceCode .= '        return $this;'."\n";
                $gettersSettersSourceCode .= "    }\n\n";
            } else {
                $typeHintingArg = $nullable ? '' : $fieldType.' ';

                $gettersSettersSourceCode .= "    /**\n";
                $gettersSettersSourceCode .= "     * @param $fieldType".($nullable ? '|null' : '')." \$$propertyName\n";
                $gettersSettersSourceCode .= "     * @return $className\n";
                $gettersSettersSourceCode .= "     */\n";
                $gettersSettersSourceCode .= "    public function " . $setterName . $setterSuffix . '('.$typeHintingArg.'$'.$propertyName.'): '.$className."\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        $this->dataSetterUsed[\''. $fieldName .'\'] = true;'."\n";
                $gettersSettersSourceCode .= '        $this->data[\''. $fieldName .'\'] = $' . $propertyName. ';'."\n";
                $gettersSettersSourceCode .= '        return $this;'."\n";
                $gettersSettersSourceCode .= "    }\n\n";
            }

            // Getters
            if($isDateType) {
                $getterName = "get" . ucfirst($propertyName);
                $getterSuffix = '';

                if(array_key_exists(strtolower($getterName), $alreadyGeneratedGetterSetter)) {
                    $getterSuffix = '_'.($alreadyGeneratedGetterSetter[strtolower($getterName)]+1);
                }


                $typeHintingReturn = $nullable ? '' : ': \DateTime';
                $gettersSettersSourceCode .= "    /**\n";
                $gettersSettersSourceCode .= "     * @return \\DateTime".($nullable ? '|null' : '')."\n";
                $gettersSettersSourceCode .= "     */\n";
                $gettersSettersSourceCode .= "    public function " . $getterName . $getterSuffix . "()$typeHintingReturn\n";
                $gettersSettersSourceCode .= "    {\n";
                $gettersSettersSourceCode .= '        if($this->data[\'' . $fieldName . '\']) {'."\n";
                $gettersSettersSourceCode .= '            return $this->convertDateTimeStringToObject(__METHOD__, (string)$this->data[\'' . $fieldName . '\']);'."\n";
                $gettersSettersSourceCode .= "        }\n";
                $gettersSettersSourceCode .= "    }\n\n";
            } else {
                $getterName = ($fieldType === 'bool' ? 'is' : 'get') . ucfirst($propertyName);
                $getterSuffix = '';

                if(array_key_exists(strtolower($getterName), $alreadyGeneratedGetterSetter)) {
                    $getterSuffix = '_'.($alreadyGeneratedGetterSetter[strtolower($getterName)]+1);
                }


                $gettersSettersSourceCode .= "    /**\n";
                $gettersSettersSourceCode .= "     * @return $fieldType".($nullable ? '|null' : '')."\n";
                $gettersSettersSourceCode .= "     */\n";
                $typeHintingReturn = $nullable ? '' : ': '.$fieldType;
                $gettersSettersSourceCode .= "    public function " . $getterName . $getterSuffix . "()$typeHintingReturn\n";
                $gettersSettersSourceCode .= "    {\n";

                if(!$nullable) {
                    $gettersSettersSourceCode .= '        return ('.$fieldType.')$this->data[\'' . $fieldName . '\'];'."\n";
                } else {
                    $gettersSettersSourceCode .= '        if(!is_null($this->data[\'' . $fieldName . '\'])) {' ."\n";
                    $gettersSettersSourceCode .= '            return ('.$fieldType.')$this->data[\'' . $fieldName . '\'];'."\n";
                    $gettersSettersSourceCode .= '        }' ."\n";
                    $gettersSettersSourceCode .= '        return null;'."\n";
                }

                $gettersSettersSourceCode .= "    }\n\n";
            }

            if(array_key_exists(strtolower($getterName), $alreadyGeneratedGetterSetter)) {
                $alreadyGeneratedGetterSetter[strtolower($getterName)]++;
            } else {
                $alreadyGeneratedGetterSetter[strtolower($getterName)] = 1;
            }

            if(array_key_exists(strtolower($setterName), $alreadyGeneratedGetterSetter)) {
                $alreadyGeneratedGetterSetter[strtolower($setterName)]++;
            } else {
                $alreadyGeneratedGetterSetter[strtolower($setterName)] = 1;
            }
        }



        $propertyDeclarationSourceCode .= "    ];\n";
        $dataSetterUsedSourceCode .= '    ];'."\n";
        $sqlFieldStructSourceCode .= '    ];'."\n";

        $primaryKeys = "    const PRIMARY_KEYS = ['" .$primaryKeys. "'];\n";

        $sourceCode .= $primaryKeys;
        $sourceCode .= "\n".$propertyDeclarationSourceCode;
        $sourceCode .= "\n".$dataSetterUsedSourceCode;
        $sourceCode .= "\n".$sqlFieldStructSourceCode;
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
            case 'date': return '1970-01-01T00:00:00.000000+00:00';
            case 'string':
            default:
                return '';
        }
    }
}
