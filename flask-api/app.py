from flask import Flask, jsonify

app = Flask(__name__)

@app.route('/ping', methods=['GET'])
def ping():
    return jsonify({"status": "ok", "message": "Flask API corriendo"})

if __name__ == '__main__':
    app.run(debug=True, port=5000)