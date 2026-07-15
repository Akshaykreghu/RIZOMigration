<style>
    body {
  font-family: Arial, sans-serif;
  text-align: center;
  margin: 0;
  padding: 0;
}

.container {
  margin-top: 50px;
}

label {
  display: block;
  margin-bottom: 10px;
}

input[type="text"] {
  padding: 5px 10px;
  margin-bottom: 15px;
}

button {
  padding: 8px 16px;
  background-color: #4CAF50;
  color: #fff;
  border: none;
  cursor: pointer;
}

button:hover {
  background-color: #45a049;
}
</style>

<head>
  <title>Remarks</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
  <div class="container">
    <label for="remarks">Enter Remarks:</label>
    <input type="text" id="remarks" placeholder="Type your remarks here...">
    <button onclick="processPayroll()">Process</button>
  </div>
  <script src="script.js"></script>
</body>

