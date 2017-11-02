import java.io.*;
import java.util.*;
import java.sql.*;
import java.text.DateFormat;
import java.text.SimpleDateFormat;

public class InsertAlert
{
	private long id;
	private long unit_id;
	private java.sql.Timestamp datetime;
	private String message;
	private String raw_input;

	private Connection cn;
	PreparedStatement pstmt;
	private String tablename;

	public InsertAlert(){}

	public String toString(){
		return "State of  InsertAlert Object :"
		+"\n\t Id = "+id 
		+"\n\t Unit_id = "+unit_id 
		+"\n\t Datetime = "+datetime 
		+"\n\t Message = "+message		
		+"\n\t Raw_input = "+raw_input;
	}	
	
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
		tablename="pt_alert";

		try
		{
			String sql = "INSERT INTO "+tablename+"(unit_id, datetime,message,raw_input) VALUES (?, ?, ?, ?)";		
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
			message = "GPS fix not available";
			raw_input = str;
		}catch(Exception e)
		{
			System.out.println("Execption in InsertAlert.java -> breakUpString() function.");			
			e.printStackTrace(System.err);
		}
	}
	
	public void insertRecord()
	{		
		try
		{
			pstmt.setLong(1, unit_id);
			pstmt.setTimestamp(2, datetime);
			pstmt.setString(3, message);			
			pstmt.setString(4, raw_input);
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
		InsertAlert insertalert = new InsertAlert();
		insertalert.connectDB();
		insertalert.preStatement();	
		insertalert.breakUpString("$1,2,28,04,09,05,18,12,000000000,0000000000,00.0,000,0,1,V#");
		insertalert.insertRecord();
		insertalert.disconnectDB();		
		System.out.println(insertalert);
	}
}