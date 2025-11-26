<?php
// Script para generar tablas detalladas de cobertura por módulo
require_once 'vendor/autoload.php';

class DetailedCoverageReporter {
    private $coverageFile = 'tests/reports/coverage.xml';
    private $junitFile = 'tests/reports/junit.xml';
    private $reportFile = 'tests/reports/detailed-coverage-tables.md';
    
    public function generateDetailedReport() {
        if (!file_exists($this->coverageFile)) {
            echo "❌ Archivo de cobertura no encontrado. Ejecute primero: .\run-tests.ps1\n";
            return;
        }
        
        $xml = simplexml_load_file($this->coverageFile);
        $modules = $this->analyzeModules($xml);
        $testResults = $this->analyzeTestResults();
        
        $markdown = $this->generateMarkdownReport($modules, $testResults);
        file_put_contents($this->reportFile, $markdown);
        
        echo "✓ Reporte detallado generado: {$this->reportFile}\n";
        $this->showSummary($modules, $testResults);
    }
    
    private function analyzeModules($xml) {
        $modules = [];
        
        // Analizar cada archivo en el XML de cobertura
        foreach ($xml->xpath('//file') as $file) {
            $fileName = (string)$file['name'];
            $fileName = str_replace('\\', '/', $fileName); // Normalizar separadores
            
            // Determinar el módulo basado en la ruta
            $module = $this->getModuleFromPath($fileName);
            
            if (!isset($modules[$module])) {
                $modules[$module] = [
                    'files' => [],
                    'total_statements' => 0,
                    'covered_statements' => 0,
                    'total_conditionals' => 0,
                    'covered_conditionals' => 0,
                    'total_methods' => 0,
                    'covered_methods' => 0
                ];
            }
            
            // Obtener métricas del archivo
            $metrics = $file->xpath('.//metrics')[0];
            if ($metrics) {
                $statements = (int)$metrics['statements'];
                $coveredStatements = (int)$metrics['coveredstatements'];
                $conditionals = (int)$metrics['conditionals'];
                $coveredConditionals = (int)$metrics['coveredconditionals'];
                $methods = (int)$metrics['methods'];
                $coveredMethods = (int)$metrics['coveredmethods'];
                
                $modules[$module]['files'][] = [
                    'name' => basename($fileName),
                    'path' => $fileName,
                    'statements' => $statements,
                    'covered_statements' => $coveredStatements,
                    'conditionals' => $conditionals,
                    'covered_conditionals' => $coveredConditionals,
                    'methods' => $methods,
                    'covered_methods' => $coveredMethods
                ];
                
                $modules[$module]['total_statements'] += $statements;
                $modules[$module]['covered_statements'] += $coveredStatements;
                $modules[$module]['total_conditionals'] += $conditionals;
                $modules[$module]['covered_conditionals'] += $coveredConditionals;
                $modules[$module]['total_methods'] += $methods;
                $modules[$module]['covered_methods'] += $coveredMethods;
            }
        }
        
        return $modules;
    }
    
    private function getModuleFromPath($filePath) {
        if (strpos($filePath, '/models/') !== false || strpos($filePath, '\\models\\') !== false) {
            return 'Models';
        } elseif (strpos($filePath, '/controller/') !== false || strpos($filePath, '\\controller\\') !== false) {
            return 'Controllers';
        } elseif (strpos($filePath, '/config/') !== false || strpos($filePath, '\\config\\') !== false) {
            return 'Config';
        } elseif (strpos($filePath, '/middleware/') !== false || strpos($filePath, '\\middleware\\') !== false) {
            return 'Middleware';
        } else {
            return 'Other';
        }
    }
    
    private function analyzeTestResults() {
        if (!file_exists($this->junitFile)) {
            return ['total' => 0, 'passed' => 0, 'failed' => 0, 'success_rate' => 0];
        }
        
        $xml = simplexml_load_file($this->junitFile);
        $testsuites = $xml->testsuite ?? $xml;
        
        $total = 0;
        $failures = 0;
        $errors = 0;
        
        foreach ($testsuites as $testsuite) {
            $total += (int)$testsuite['tests'];
            $failures += (int)$testsuite['failures'];
            $errors += (int)$testsuite['errors'];
        }
        
        $passed = $total - $failures - $errors;
        $successRate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failures + $errors,
            'success_rate' => $successRate
        ];
    }
    
    private function generateMarkdownReport($modules, $testResults) {
        $date = date('Y-m-d H:i:s');
        
        $markdown = "# Reporte Detallado de Cobertura de Código\n\n";
        $markdown .= "**Sistema Hotel** - Generado el: {$date}\n\n";
        
        // 6.2.1.1 Cobertura de Declaraciones
        $markdown .= "## 6.2.1.1 Cobertura de Declaraciones\n\n";
        $markdown .= "| Módulo | Sentencias cubiertas | Sentencias totales | Cobertura (%) |\n";
        $markdown .= "|--------|---------------------|-------------------|---------------|\n";
        
        $totalStatements = 0;
        $totalCoveredStatements = 0;
        
        foreach ($modules as $moduleName => $module) {
            if ($module['total_statements'] > 0) {
                $coverage = round(($module['covered_statements'] / $module['total_statements']) * 100, 2);
                $markdown .= "| {$moduleName} | {$module['covered_statements']} | {$module['total_statements']} | {$coverage}% |\n";
                
                $totalStatements += $module['total_statements'];
                $totalCoveredStatements += $module['covered_statements'];
            }
        }
        
        $overallStatementsPercent = $totalStatements > 0 ? round(($totalCoveredStatements / $totalStatements) * 100, 2) : 0;
        $markdown .= "| **TOTAL** | **{$totalCoveredStatements}** | **{$totalStatements}** | **{$overallStatementsPercent}%** |\n\n";
        
        // 6.2.1.2 Cobertura de Ramas
        $markdown .= "## 6.2.1.2 Cobertura de Ramas\n\n";
        $markdown .= "| Módulo | Ramas cubiertas | Ramas totales | Cobertura (%) |\n";
        $markdown .= "|--------|----------------|---------------|---------------|\n";
        
        $totalConditionals = 0;
        $totalCoveredConditionals = 0;
        
        foreach ($modules as $moduleName => $module) {
            if ($module['total_conditionals'] > 0) {
                $coverage = round(($module['covered_conditionals'] / $module['total_conditionals']) * 100, 2);
                $markdown .= "| {$moduleName} | {$module['covered_conditionals']} | {$module['total_conditionals']} | {$coverage}% |\n";
                
                $totalConditionals += $module['total_conditionals'];
                $totalCoveredConditionals += $module['covered_conditionals'];
            }
        }
        
        $overallConditionalsPercent = $totalConditionals > 0 ? round(($totalCoveredConditionals / $totalConditionals) * 100, 2) : 0;
        $markdown .= "| **TOTAL** | **{$totalCoveredConditionals}** | **{$totalConditionals}** | **{$overallConditionalsPercent}%** |\n\n";
        
        // 6.2.1.3 Cobertura de Caminos (Métodos)
        $markdown .= "## 6.2.1.3 Cobertura de Caminos (Métodos)\n\n";
        $markdown .= "| Módulo | Métodos cubiertos | Métodos totales | Cobertura (%) |\n";
        $markdown .= "|--------|------------------|----------------|---------------|\n";
        
        $totalMethods = 0;
        $totalCoveredMethods = 0;
        
        foreach ($modules as $moduleName => $module) {
            if ($module['total_methods'] > 0) {
                $coverage = round(($module['covered_methods'] / $module['total_methods']) * 100, 2);
                $markdown .= "| {$moduleName} | {$module['covered_methods']} | {$module['total_methods']} | {$coverage}% |\n";
                
                $totalMethods += $module['total_methods'];
                $totalCoveredMethods += $module['covered_methods'];
            }
        }
        
        $overallMethodsPercent = $totalMethods > 0 ? round(($totalCoveredMethods / $totalMethods) * 100, 2) : 0;
        $markdown .= "| **TOTAL** | **{$totalCoveredMethods}** | **{$totalMethods}** | **{$overallMethodsPercent}%** |\n\n";
        
        // 6.2.2 Tasa de éxito en pruebas
        $markdown .= "## 6.2.2 Tasa de Éxito en Pruebas\n\n";
        $markdown .= "| Métrica | Valor |\n";
        $markdown .= "|---------|-------|\n";
        $markdown .= "| Pruebas ejecutadas | {$testResults['total']} |\n";
        $markdown .= "| Pruebas exitosas | {$testResults['passed']} |\n";
        $markdown .= "| Pruebas fallidas | {$testResults['failed']} |\n";
        $markdown .= "| **Tasa de éxito** | **{$testResults['success_rate']}%** |\n\n";
        
        // Detalle por archivo
        $markdown .= "## Detalle por Archivo\n\n";
        foreach ($modules as $moduleName => $module) {
            if (!empty($module['files'])) {
                $markdown .= "### {$moduleName}\n\n";
                $markdown .= "| Archivo | Sentencias | Ramas | Métodos |\n";
                $markdown .= "|---------|------------|-------|----------|\n";
                
                foreach ($module['files'] as $file) {
                    $stmtPercent = $file['statements'] > 0 ? round(($file['covered_statements'] / $file['statements']) * 100, 2) : 0;
                    $condPercent = $file['conditionals'] > 0 ? round(($file['covered_conditionals'] / $file['conditionals']) * 100, 2) : 0;
                    $methodPercent = $file['methods'] > 0 ? round(($file['covered_methods'] / $file['methods']) * 100, 2) : 0;
                    
                    $markdown .= "| {$file['name']} | {$stmtPercent}% ({$file['covered_statements']}/{$file['statements']}) | {$condPercent}% ({$file['covered_conditionals']}/{$file['conditionals']}) | {$methodPercent}% ({$file['covered_methods']}/{$file['methods']}) |\n";
                }
                $markdown .= "\n";
            }
        }
        
        return $markdown;
    }
    
    private function showSummary($modules, $testResults) {
        echo "\n=== RESUMEN DE COBERTURA ===\n";
        
        $totalStatements = 0;
        $totalCoveredStatements = 0;
        
        foreach ($modules as $module) {
            $totalStatements += $module['total_statements'];
            $totalCoveredStatements += $module['covered_statements'];
        }
        
        $overallPercent = $totalStatements > 0 ? round(($totalCoveredStatements / $totalStatements) * 100, 2) : 0;
        
        echo "Cobertura General: {$overallPercent}% ({$totalCoveredStatements}/{$totalStatements})\n";
        echo "Tasa de Éxito: {$testResults['success_rate']}% ({$testResults['passed']}/{$testResults['total']})\n";
        echo "\nPor módulo:\n";
        
        foreach ($modules as $moduleName => $module) {
            if ($module['total_statements'] > 0) {
                $percent = round(($module['covered_statements'] / $module['total_statements']) * 100, 2);
                echo "- {$moduleName}: {$percent}%\n";
            }
        }
    }
}

// Ejecutar el reporte detallado
$reporter = new DetailedCoverageReporter();
$reporter->generateDetailedReport();
?>