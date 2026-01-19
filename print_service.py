from flask import Flask, request, jsonify
from flask_cors import CORS
from escpos.printer import Win32Raw

app = Flask(__name__)
CORS(app)  # Enable CORS for browser requests

@app.route('/print', methods=['POST'])
def print_receipt():
    try:
        data = request.json

        printer_name = data['printer_name']
        receipt = data['receipt']

        printer = Win32Raw(printer_name)
        printer._raw(receipt.encode('utf-8'))
        printer.cut()

        # Open cash drawer (pulse to pin 2)
        try:
            printer._raw(b'\x1B\x70\x00\x3C\xFF')
        except:
            pass  # Ignore if cash drawer command fails

        printer.close()

        return jsonify({
            'status': 'ok',
            'message': 'Printed successfully'
        })

    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/ping')
def ping():
    return 'OK'


if __name__ == '__main__':
    print('🖨️ USB Print Service starting...')
    print('📍 Running at http://127.0.0.1:9100')
    print('💡 Test with: curl http://127.0.0.1:9100/ping')
    app.run(host='127.0.0.1', port=9100, debug=False)
