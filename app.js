var fs = require("fs");
var os = require("os");

//requeires latest node version !!!!!!
String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};


var templateDirectory = "./Templates";
var targetDirectory = "./";
var indexFile = "./index.php";
var name = "test";


var dependecies = [];

applyDirectoryTransform(templateDirectory);
setTimeout(() => {
    addDependecies(indexFile);
}, 1000);

function addDependecies(file) {
    var lineReader = require('readline').createInterface({
        input: require('fs').createReadStream(file)
    });
    var fileContents = "";
    lineReader.on('line', function (line) {
        if (line.charAt(0) !== "r" && line.charAt(0) !== "<" && line.charAt(0) !== "/") {
            dependecies.forEach(dp => {
                var validName = dp.replace(".//", "").replace("./", "");
                fileContents += `require('${validName}');` + os.EOL;
            });
            dependecies = [];
        }
        fileContents += line + os.EOL;
    });
    lineReader.on("close", () => {
        fs.writeFile(file, fileContents, (err) => {
            if (err) throw err;
        });
    });
}
function applyDirectoryTransform(dir) {
    fs.exists(dir, (exists) => {
        if (exists) {
            fs.readdir(dir, (err, files) => {
                if (err) throw err;
                files.forEach(function (file) {
                    applyTransform(dir + "/" + file);
                }, this);
            })
                ;
        } else {
            fs.mkdir(dir, (err) => {
                if (err) throw err;
                fs.readdir(dir, (err, files) => {
                    if (err) throw err;
                    files.forEach(function (file) {
                        applyTransform(dir + "/" + file);
                    }, this);
                });
            });
        }
    });
}
function applyFileTransform(filename) {
    let file = filename;
    fs.readFile(file, (err, data) => {
        if (err) throw err;
        var filename = applyFileNameTransform(file);
        var fileContent = applyTextTransform(data.toString());
        dependecies.push(filename);
        fs.writeFile(filename, fileContent, (err) => {
            if (err) throw err;
        });
    });
}
function applyFileNameTransform(filename) {
    return applyTextTransform(filename).replaceAll(".tp", ".php").replaceAll(templateDirectory, targetDirectory);
}
function applyTextTransform(text) {
    return text.replaceAll("{Name}", capitalizeFirstLetter(name)).replaceAll("{name}", lowerCaseFirstLetter(name));
}
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
function lowerCaseFirstLetter(string) {
    return string.charAt(0).toLowerCase() + string.slice(1);
}
function applyTransform(filename) {
    fs.stat(filename, (err, stat) => {
        if (err) throw err;
        if (stat.isDirectory()) {
            applyDirectoryTransform(filename);
        } else {
            applyFileTransform(filename);
        }
    });
}
