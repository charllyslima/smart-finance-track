<?php

namespace App\Http\Controllers;

use App\Models\BrokerageStatement;
use App\Models\Negotiation;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Smalot\PdfParser\Parser;
use App\Http\Requests\ImportBrokerageStatementRequest;

class BrokerageStatementController extends Controller
{

    public function importBrokerageStatement(ImportBrokerageStatementRequest $request): JsonResponse
    {

        // Obter os dados validados
        $validated = $request->validated();

        // Obter o ID do broker
        $brokerId = $validated['broker_id'];

        // Obter o arquivo
        $file = $validated['brokerage_statement'];

        // Ler o conteúdo do arquivo PDF
        $parser = new Parser();
        $pdf = $parser->parseFile($file->getRealPath());

        // Extrair texto do PDF
        $text = $pdf->getText();
        return $this->processArchive($text, (int)$brokerId);
        
    }

    private function processArchive(string $text, int $brokerId): mixed
    {

        $lines = explode("\n", $text);

        $brokerageStatement = new BrokerageStatement();
        $brokerageStatement->broker_id = $brokerId;
        $brokerageStatement->user_id = Auth::id();
        $brokerageStatement->fees_and_taxes = 0;
        $brokerageStatement->status = 'pending';

        $brokerageStatement = match ($brokerId) {
            1 => $this->processArchiveClear($brokerageStatement, $lines),
            2 => $this->processArchiveAgora($brokerageStatement, $lines),
            3 => $this->processArchiveBTG($brokerageStatement, $lines),
            default => null,
        };

        if ($brokerageStatement === null) {
            return response()->json(['message' => 'Falha ao importar nota de negociação.'], 500);
        }

        return response()->json([$brokerageStatement], 200);
    }

    private function processAgoraArchive($text): array
    {
        $operation = explode("\n", $text);
        $operation = array_filter($operation, static function ($d) {
            return !empty($d);
        });
        $operation = array_values($operation);
        return $operation;
    }

    private function processNegotiationClear($line): Negotiation
    {
        $operation = explode(' ', $line);

        $operation = array_filter($operation, static function ($d) {
            return !empty($d);
        });

        $operation = array_values($operation);
        $negociation = new Negotiation();

        foreach ($operation as $key => $data) {
            if (str_contains($data, '11')) {
                $negociation->acronym = str_replace('11', '', $data);
                $negociation->quantity = (int)preg_replace('/\D/', '', $operation[count($operation) - 2]);
                $patterns = ['/(1-BOVESPA)/', '/(VISTA\tFII)/'];
                if ($operation[0] === '1-BOVESPA') {
                    $negociation->type = preg_replace($patterns[1], '', $operation[1]);
                } else {
                    $negociation->type = preg_replace($patterns, '', $operation[0]);
                }
                $price = explode('	', end($operation));
                $negociation->price = (float)str_replace(',', '.', $price[0]);
                $negociation->total = (float)str_replace(',', '.', preg_replace('/[^0-9,]/', '', $price[1]));
            }
        }
        return $negociation;
    }

    private function processArchiveClear(BrokerageStatement $brokerageStatement, array $text): mixed
    {

        $startMarker = 'Negócios realizados';
        $endMarker = 'NOTA DE NEGOCIAÇÃO';
        $collecting = false;
        $negotiations = [];

        foreach ($text as $index => $line) {
            if ($collecting) {
                $negotiations[] = $line;
            }
            switch ($line) {
                case $startMarker:
                    $collecting = true;
                    break;
                case $endMarker:
                    $collecting = false;
                    break;
                case 'Nr. nota':
                    $brokerageStatement->note_number = $text[$index + 1];
                    break;
                case 'Data pregão':
                    $brokerageStatement->trade_date  = DateTime::createFromFormat('d/m/Y', $text[$index + 1])->format('Y-m-d');
                    break;
                case str_contains($line, "Valor líquido das operações"):
                    $brokerageStatement->net_operations_value = (float)str_replace(',', '.', preg_replace('/[^0-9,]/', '', $line));
                    break;
                case str_contains($line, "Taxa A.N.A."):
                case str_contains($line, "Taxa de Registro"):
                case str_contains($line, "Taxa de liquidação"):
                case str_contains($line, "Taxa de termo/opções"):
                case str_contains($line, "Taxa Operacional"):
                case str_contains($line, "Taxa de Custódia"):
                case str_contains($line, "Execução"):
                case str_contains($line, "Impostos"):
                case str_contains($line, "Emolumentos"):
                    $brokerageStatement->fees_and_taxes += (float)str_replace(',', '.', preg_replace('/[^0-9,]/', '', $line));
                    break;
                case str_contains($line, "I.R.R.F. s/ operações, base"):
                    $line = str_replace('I.R.R.F. s/ operações, base R$0,00', '', $line);
                    $brokerageStatement->fees_and_taxes += (float)str_replace(',', '.', $line);
                    break;
            }
        }

        array_shift($negotiations);
        array_pop($negotiations);
        $result = [];
        foreach ($negotiations as $negotiation) {
            $result[] = $this->processNegotiationClear($negotiation);
        }

        return ['brokerageStatement' => $brokerageStatement, 'negotiations' => $result];
    }

    private function processArchiveAgora(BrokerageStatement $brokerageStatement, array $text): BrokerageStatement
    {
        $startMarker = 'Negócios realizados';
        $endMarker = 'NOTA DE NEGOCIAÇÃO';
        $collecting = false;
        $negotiations = [];
        foreach ($text as $index => $line) {
        }
        return $brokerageStatement;
    }

    private function processArchiveBTG(BrokerageStatement $brokerageStatement, array $text): BrokerageStatement
    {
        return $brokerageStatement;
    }
}
