var appToken = new AppToken();
describe("Wfst", function(){
	it("Returns Id from Popup HTML", function(){
		var baseAPIURL = 'https://devapi.regionalroads.com'
		var wfstLayer = new WfstLayer('Test_Line_dev', appToken, baseAPIURL)
		var html = `<html>
		<head>
		  <title>
			
		  </title>
		</head>
		<style type="text/css">
		  table.featureInfo, table.featureInfo td, table.featureInfo th {
					  border:1px solid #ddd;
					  border-collapse:collapse;
					  margin:0;
					  padding:0;
					  font-size: 90%;
					  padding:.2em .1em;
		  }
		  table.featureInfo{
		  
		  }
			  table.featureInfo th{
				  padding:.2em .2em;
					  text-transform:uppercase;
					  font-weight:bold;
					  background:#eee;
			  }
			  table.featureInfo td{
					  background:#fff;
			  }
			  table.featureInfo tr.odd td{
					  background:#eee;
			  }
			  table.featureInfo caption{
					  text-align:left;
					  font-size:100%;
					  font-weight:bold;
					  text-transform:uppercase;
					  padding:.2em .2em;
			  }
		</style>
		<body>
		<table>
			<tr><td>OBJECTID:</td><td>4</td></tr>
			<tr><td>ClosureName:</td><td>Temporary Partial Road Closure: Granville Street Curb Lanes Closed until October 31, 2020 (All hours of the day)</td></tr>
			<tr><td>StartDate:</td><td>Jul 16, 2020 7:00:00 AM</td></tr>
			<tr><td>EndDate:</td><td>Nov 1, 2020 6:59:59 AM</td></tr>
			<tr><td>Status:</td><td>Active</td></tr>
			<tr><td>Link:</td><td><p>https://vancouver.ca/your-government/contact-the-city-of-vancouver.aspx</p></td></tr>
			<tr><td>Notes:</td><td></td></tr>
			<tr><td>ClosureDescription:</td><td><p>The curb lanes on Granville Street in the City of Vancouver are closed to vehicular traffic between Broadway and 16th Avenue during all hours of the day. This is a temporary closure effective through October 31, 2020. Please contact the City of Vancouver for additional information.</p></td></tr>
		</table>
		</li>
		</body>
	  </html>
	  `;	  
	  console.log(wfstLayer);
	  expect(wfstLayer.getIDFromPopup(html)).toEqual(4);
	});
});
