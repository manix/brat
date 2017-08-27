(function (output) {
  "use strict";

  var endpoint = "<?= $url ?>";
  var __t8path = null;
  var files = {};

  if (!output) {
    output = {};
  }

  output.load = function (path) {
    return new Promise((resolve, reject) => {
      if (files[path] === undefined) {
        fetch(endpoint + "?path=" + path, {
          headers: {
            Accept: "application/json"
          }
        }).catch(reject).then(resp => resp.json()).then(json => {
          files[path] = json;
          resolve(json);
        });
      } else {
        resolve(files[path]);
      }
    });
  };

  output.t8 = function (path, string = null, data = null) {
    if (string === null) {
      string = path;
      path = __t8path;
    }

    var t = this.getTranslatedStrings(path)[string] || (path + ":" + string);

    if (data === null) {
      return t;
    } else {
      return t.replace(/{\$(\d+)}/g, (match, content) => data[content] || "");
  }
  };

  output.getTranslatedStrings = function (path) {
    if (files[path] === undefined) {
      throw new Error("Trying to use translations that have not been loaded.");
    }

    return files[path];
  };

})(module ? module.exports : this.brt)