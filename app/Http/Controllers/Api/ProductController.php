<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\SoapHelper;

class ProductController extends Controller
{
    function wsdl()
    {
        return response()->file(storage_path('app/public/product.wsdl'), [
            'Content-Type' => 'application/xml'
        ]);
    }

    function read(Request $request)
    {
        try 
        {
            $xml = simplexml_load_string($request->getContent());
            $data = SoapHelper::parseSoapRequest($xml, ['id']);

            $product = Product::find($data['id']);
            if($product)
            {
                $xmlResponse = SoapHelper::soapSuccessResponse('readProductResponse', [
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price
                    ]
                ]);

                return response()->make($xmlResponse, 200, ['Content-Type' => 'application/soap+xml']);
            }else
            {
                return SoapHelper::soapFaultResponse('Client', 'Product not found');
            }
        }catch(\Exception $e) 
        {
            return SoapHelper::soapFaultResponse('Server', 'Invalid XML format or server error');
        }
    }

    function create(Request $request)
    {
        try 
        {
            $xml = simplexml_load_string($request->getContent());
            $data = SoapHelper::parseSoapRequest($xml, ['name', 'description', 'price']);

            $validator = Validator::make($data, [
                'name' => 'required|max:255',
                'description' => 'required',
                'price' => 'required|numeric|min:0',
            ]);

            if($validator->fails())
            {
                return SoapHelper::soapFaultResponse('Client', $validator->errors()->first());
            }

            $product = Product::create($data);

            $xmlResponse = SoapHelper::soapSuccessResponse('createProductResponse', [
                'message' => 'Product created successfully',
                'productId' => $product->id
            ]);

            return response()->make($xmlResponse, 201, ['Content-Type' => 'application/soap+xml']);
        }catch(\Exception $e) 
        {
            return SoapHelper::soapFaultResponse('Server', 'Invalid XML format or server error');
        }
    }

    function update(Request $request)
    {
        try 
        {
            $xml = simplexml_load_string($request->getContent());
            $data = SoapHelper::parseSoapRequest($xml, ['id', 'name', 'description', 'price']);

            $validator = Validator::make($data, [
                'id' => 'required|numeric',
                'name' => 'required|max:255',
                'description' => 'required',
                'price' => 'required|numeric|min:0',
            ]);

            if($validator->fails()) 
            {
                return SoapHelper::soapFaultResponse('Client', $validator->errors()->first());
            }

            $product = Product::find($data['id']);
            if($product)
            {
                $product->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price']
                ]);

                $xmlResponse = SoapHelper::soapSuccessResponse('updateProductResponse', [
                    'message' => 'Product updated successfully',
                    'productId' => $product->id
                ]);

                return response()->make($xmlResponse, 200, ['Content-Type' => 'application/soap+xml']);
            }else
            {
                return SoapHelper::soapFaultResponse('Client', 'Product not found');
            }
        }catch(\Exception $e) 
        {
            return SoapHelper::soapFaultResponse('Server', 'Invalid XML format or server error');
        }
    }

    function delete(Request $request)
    {
        try 
        {
            $xml = simplexml_load_string($request->getContent());
            $data = SoapHelper::parseSoapRequest($xml, ['id']);

            $product = Product::find($data['id']);
            if($product)
            {
                $product->delete();

                $xmlResponse = SoapHelper::soapSuccessResponse('deleteProductResponse', [
                    'message' => 'Product deleted successfully',
                    'productId' => $data['id']
                ]);

                return response()->make($xmlResponse, 200, ['Content-Type' => 'application/soap+xml']);
            }else
            {
                return SoapHelper::soapFaultResponse('Client', 'Product not found');
            }
        }catch(\Exception $e) 
        {
            return SoapHelper::soapFaultResponse('Server', 'Invalid XML format or server error');
        }
    }

}