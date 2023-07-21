describe("Edit Map", function(){
    var editMap;
    var appToken;
    var options = {};
    beforeEach(function(){
	appToken = new AppToken();
	editMap = new EditMap(appToken, "mapId", options);
	
    });
     it("Should create editMap instance", function(){
	expect(editMap).toEqual(jasmine.anything());
    });
    it("should return sum", function(){
	expect(editMap.sum(2,2)).toEqual(4);
    });
});
