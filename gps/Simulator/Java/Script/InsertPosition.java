import java.io.*;
import java.util.*;
import java.sql.*;
import java.text.DateFormat;
import java.text.SimpleDateFormat;

public class InsertPosition
{
	private long id;
	private long unit_id;
	private java.sql.Timestamp datetime;
	private java.sql.Timestamp datetime_received;
	private double lat;
	private double lon;
	private double alt;
	private double deg;
	private double speed_km;
	private double speed_kn;
	private long sattotal;
	private short fixtype;
	private String raw_input;
	private String hash;	
	
	private String tablename;

	private Connection cn;
	PreparedStatement pstmt;
	
	public InsertPosition(){}

	public void connectDB()
	{
		if(cn==null)
		{
			try
			{
				Class.forName("com.mysql.jdbc.Driver");       		
				cn = DriverManager.getConnection("jdbc:mysql://localhost:3306/csl_db?user=root&password=");			
			}catch(ClassNotFoundException cnfe){
				cnfe.printStackTrace(System.err);
			}catch(SQLException se){
				se.printStackTrace(System.err);
			}
		}
	}
	
	public void preStatement()
	{
		tablename="pt_position";
		try
		{
			String sql = "INSERT INTO "+tablename+"(unit_id, datetime, datetime_received, lat, lon, alt, deg, speed_km, speed_kn, sattotal, fixtype, raw_input, hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";		
			pstmt = cn.prepareStatement(sql);
		}catch(SQLException se){
			se.printStackTrace(System.err);
		}
	}
	public void executeDML()
	{
		try
		{
			int rowCount = pstmt.executeUpdate();     		
		}catch(SQLException se){
			se.printStackTrace(System.err);
		}catch(Exception se){
			se.printStackTrace(System.err);
		}		
	}

	public void breakUpString(String str)
	{
		try
		{
			StringTokenizer st = new StringTokenizer(str,",");
				
			String v1 = st.nextToken();
			String v2 = st.nextToken();
			
			String v3 = st.nextToken();
			String v4 = st.nextToken();
			String v5 = st.nextToken();
			
			String v6 = st.nextToken();		
			String v7 = st.nextToken();
			String v8 = st.nextToken();
			
			String v9 = st.nextToken();
			String v10 = st.nextToken();
			String v11 = st.nextToken();
			String v12 = st.nextToken();				
			String v13 = st.nextToken();
			String v14 = st.nextToken();
				
			int yy = Integer.parseInt(v5)+2000;
			
			String vE1 = yy+"-"+v4+"-"+v3+" "+v6+":"+v7+":"+v8;
					
			DateFormat df = new SimpleDateFormat ("yyyy-MM-dd HH:mm:ss");
	
	    	java.util.Date d1 = df.parse(vE1);
			
			unit_id = Long.parseLong(v2);	  
			datetime = new java.sql.Timestamp(d1.getTime());
			datetime_received = new java.sql.Timestamp((new java.util.Date()).getTime());
			
			lat = getLatitude(v9);
			lon = getLongitude(v10);
			
			alt = 62;
			deg =  Double.parseDouble(v12);
			
			speed_km = Double.parseDouble(v11);
			speed_kn = speed_km / 1.852;
			
			sattotal = 1;
			fixtype = 3;
			raw_input = str;
			hash = "";
		}catch(Exception e)
		{
			System.out.println("Execption in InsertPosition -> breakUpString() function.");
			e.printStackTrace(System.err);
		}
	}

	public double getLatitude(String val)
	{
		int sign=1;
		double d=0;
		
		String str0 = val.substring(0,2);
		String str1 = val.substring(2,val.length()-1);
		String str2 = val.substring(val.length()-1);
		
		float  dlat = Float.parseFloat(str1);
		
		double f = dlat / 60.0;
		
		double dlat1 = (double) f;
		
		String ss1 = dlat1+"";
		
		int pp1 = ss1.indexOf(".");
		
		String ss2 = ss1.substring(0,pp1);
		String ss3 = ss1.substring((pp1+1));
		
		String str = str0+"."+ss2+ss3;
		
		if(str2.equalsIgnoreCase("S"))
		{
			sign = -1;
		}
		
		d = Double.parseDouble(str)*sign;
		
		return d;

	}
	
	public double getLongitude(String val)
	{
		int sign=1; double d=0;
		
		String str0 = val.substring(0,3);
		String str1 = val.substring(3,val.length()-1);
		String str2 = val.substring(val.length()-1);
		
		float  dlat = Float.parseFloat(str1);
		
		double f = dlat / 60.0;
		
		
		double dlat1 = (double) f;
		
		String ss1 = dlat1+"";
		
		int pp1 = ss1.indexOf(".");
		
		String ss2 = ss1.substring(0,pp1);
		String ss3 = ss1.substring((pp1+1));
		
		String str = str0+"."+ss2+ss3;
		
		if(str2.equalsIgnoreCase("W"))
		{
			sign = -1;
		}
		
		d = Double.parseDouble(str)*sign;
		
		return d;
	}	
	
	public void insertRecord()
	{		
		try
		{
			pstmt.setLong(1, unit_id);
			pstmt.setTimestamp(2, datetime);
			pstmt.setTimestamp(3, datetime_received);
			pstmt.setDouble(4, lat);
			pstmt.setDouble(5, lon);
			pstmt.setDouble(6, alt);
			pstmt.setDouble(7, deg);
			pstmt.setDouble(8, speed_km);
			pstmt.setDouble(9, speed_kn);
			pstmt.setLong(10, sattotal);
			pstmt.setShort(11, fixtype);
			pstmt.setString(12, raw_input);
			pstmt.setString(13, hash);		
			executeDML();
		}catch(Exception e)
		{
			e.printStackTrace(System.err);
		}
	}
	
	public void disconnectDB()
	{
		if(cn!=null)
		{
			try
			{
				cn.close();
			}catch(SQLException se){
				se.printStackTrace(System.err);
			}
		}	
	}

	public static void main(String [] args)
	{
		InsertPosition insertposition = new InsertPosition();
		insertposition.connectDB();
		insertposition.preStatement();	
		insertposition.breakUpString("$1,2,04,10,11,15,17,35,26473636S,075497690W,00.0,348,5,0,A#");
		insertposition.insertRecord();
		insertposition.disconnectDB();
	}
}